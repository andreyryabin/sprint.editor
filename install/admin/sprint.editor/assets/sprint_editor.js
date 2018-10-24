var sprint_editor = {
    _templates: {},
    _tplcache: {},
    _parameters: {},
    _registered: {},
    _events: {},
    _entries: {},
    _uidcounter: 0,
    _imagesdelete: {},
    _submitcnt: 0,

    registerBlock: function (name, method) {
        this._registered[name] = method;
    },

    registerTemplates: function (templates) {
        this._templates = templates
    },

    registerParameters: function (parameters) {
        this._parameters = parameters
    },

    registerTemplate: function (name, html) {
        this._templates[name] = html;
    },

    hasBlockMethod: function (name) {
        return !!this._registered[name];
    },

    getBlockMethod: function (name) {
        return (this._registered[name]) ? this._registered[name] : false;
    },

    hasBlockParams: function (name) {
        return !!this._parameters[name];
    },

    getBlockParams: function (name) {
        return (this._parameters[name]) ? this._parameters[name] : false;
    },

    getBlockWebPath: function (blockName) {
        var values = this.getBlockParams(blockName);
        if (values.islocal) {
            return '/local/admin/sprint.editor/' + values.groupname + '/' + values.name;
        } else {
            return '/bitrix/admin/sprint.editor/' + values.groupname + '/' + values.name;
        }
    },

    renderTemplate: function (name, data) {
        if (!this._tplcache[name]) {
            var $tpl = jQuery('#sp-x-template-' + name);
            if ($tpl.length > 0) {
                this._tplcache[name] = $tpl.html();
            } else {
                this._tplcache[name] = '';
            }
        }

        if (window.doT) {
            var func = window.doT.template(
                this._tplcache[name]
            );
            return func(data);
        }

        return '';
    },

    markImagesForDelete: function (items) {
        this._imagesdelete = jQuery.extend({}, this._imagesdelete, items);
    },

    deleteImagesBeforeSubmit: function () {
        this._submitcnt++;

        if (this._submitcnt !== 1) {
            return;
        }

        if (jQuery.isEmptyObject(this._imagesdelete)) {
            return;
        }

        jQuery.ajax({
            url: '/bitrix/admin/sprint.editor/assets/backend/delete.php',
            type: 'post',
            data: {
                items: this._imagesdelete
            }
        });

    },

    initblock: function ($, $el, name, blockData, blockSettings) {
        name = sprint_editor.hasBlockMethod(name) ? name : 'dump';

        var method = sprint_editor.getBlockMethod(name);
        var entry = new method($, $el, blockData, blockSettings);

        var html = sprint_editor.renderTemplate(name, entry.getData());
        $el.html(html).addClass('sp-block-' + name);

        if (typeof entry.afterRender == 'function') {
            entry.afterRender();
        }

        return entry;
    },

    initblockAreas: function ($, $el, entry) {
        if (entry && typeof entry.getAreas == 'function') {
            var areas = entry.getAreas();
            var entryData = entry.getData();
            for (var prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    var area = areas[prop];

                    var areaData = {};
                    if (area.dataFrom) {
                        areaData[area.dataFrom] = entryData[area.dataKey];
                    } else {
                        areaData = entryData[area.dataKey];
                    }

                    area.block = sprint_editor.initblock(
                        $,
                        $el.find(area.container),
                        area.blockName,
                        areaData
                    );
                }
            }
        }
    },

    beforeDelete: function (uid) {
        if (!sprint_editor._entries[uid]) {
            return;
        }

        var entry = sprint_editor._entries[uid];

        if (typeof entry.beforeDelete === 'function') {
            entry.beforeDelete();
        }

        if (typeof entry.getAreas === 'function') {
            var areas = entry.getAreas();
            for (var prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    var area = areas[prop];
                    if (typeof area.block.beforeDelete === 'function') {
                        area.block.beforeDelete();
                    }
                }
            }
        }


    },

    collectData: function (uid) {
        var blockData = {};
        if (!sprint_editor._entries[uid]) {
            return blockData;
        }

        var entry = sprint_editor._entries[uid];

        if (typeof entry.collectData !== 'function') {
            return blockData;
        }

        blockData = entry.collectData();

        if (typeof entry.getAreas !== 'function') {
            return blockData;
        }

        var areas = entry.getAreas();
        for (var prop in areas) {
            if (areas.hasOwnProperty(prop)) {
                var area = areas[prop];
                var adata = area.block.collectData();
                if (area.dataFrom) {
                    blockData[area.dataKey] = adata[area.dataFrom];
                } else {
                    blockData[area.dataKey] = adata;
                }

            }
        }

        return blockData;
    },

    fireEvent: function (type) {
        if (!this._events[type]) {
            this._events[type] = [];
        }

        for (var prop in this._events[type]) {
            if (this._events[type].hasOwnProperty(prop)) {
                var event = this._events[type][prop];
                if (typeof event === 'function') {
                    event();
                }
            }
        }
    },

    listenEvent: function (type, callback) {
        if (!this._events[type]) {
            this._events[type] = [];
        }
        this._events[type].push(callback);
    },

    copyToClipboard: function (uid) {
        if (window.localStorage && sprint_editor._entries[uid]) {

            var blockData = sprint_editor.collectData(uid);
            var val = JSON.parse(
                localStorage.getItem('sprint-editor-cb01')
            );

            val = (val) ? val : {};

            if (val[uid]) {
                delete val[uid];
            } else {
                val[uid] = blockData;
            }

            localStorage.setItem("sprint-editor-cb01", JSON.stringify(val));
            this.fireEvent('copy');
        }


    },

    clearClipboard: function () {
        if (window.localStorage) {
            localStorage.removeItem('sprint-editor-cb01');
            this.fireEvent('copy');
        }

    },

    getClipboard: function () {
        var val = {};
        if (window.localStorage) {
            val = JSON.parse(
                localStorage.getItem('sprint-editor-cb01')
            );
        }

        return (val) ? val : {};
    },

    makeUid: function (prefix) {
        var uniq = Math.random().toString(36).substring(2, 12);
        this._uidcounter++;

        prefix = (prefix) ? prefix : 'sp';

        return prefix + this._uidcounter + uniq;
    },

    renderBlock: function (blockData, blockSettings, uid, params) {
        var renderVars = this.getBlockParams(blockData.name);

        renderVars.uid = uid;
        renderVars.showSortButtons = params.showSortButtons;
        renderVars.showCopyButtons = params.showCopyButtons;
        renderVars.enableChange = params.enableChange;

        renderVars.compiled = this.compileSettings(blockData, blockSettings);

        return this.renderTemplate('box', renderVars);
    },

    compileSettings: function (blockData, settings) {

        var compiled = [];

        if (!settings) {
            return compiled;
        }

        jQuery.each(settings, function (setName, setSet) {

            if (!setSet.value || !setSet.type || setSet.type != 'select') {
                return true;
            }

            var value = [];
            var valSel = 0;

            jQuery.each(setSet.value, function (valVal, valTitle) {

                valSel = (
                    blockData.settings &&
                    blockData.settings[setName] &&
                    blockData.settings[setName] == valVal
                ) ? 1 : 0;

                value.push({
                    title: valTitle,
                    value: valVal,
                    selected: valSel
                })
            });
            compiled.push({
                name: setName,
                options: value
            })
        });

        return compiled;
    },

    getBlockSettings: function (name, params) {
        var blockSettings = {};
        if (params && params.jsonUserSettings && params.jsonUserSettings.block_settings) {
            if (params.jsonUserSettings.block_settings[name]) {
                blockSettings = params.jsonUserSettings.block_settings[name];
            }
        }

        return blockSettings;
    },


    create: function ($, params) {
        var $editor = $('.sp-x-editor' + params.uniqid);
        var $inputresult = $('.sp-x-result' + params.uniqid);
        var $form = $editor.closest('form').first();

        $editor.on('keypress', 'input', function (e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $('form input').on('keypress', function (e) {
            return e.which !== 13;
        });

        if (!params.jsonValue) {
            params.jsonValue = {};
        }

        if (!params.jsonValue.blocks) {
            params.jsonValue.blocks = [];
        }

        if (!params.jsonValue.layouts) {
            params.jsonValue.layouts = [];
        }

        if (!params.jsonUserSettings) {
            params.jsonUserSettings = {};
        }

        $.each(params.jsonValue.layouts, function (index, layout) {
            layoutAdd(layout);
        });

        $.each(params.jsonValue.blocks, function (index, block) {
            blockAdd(block);
        });

        sprint_editor.listenEvent('focus', function () {
            checkClipboardButtons();
        });

        sprint_editor.listenEvent('copy', function () {
            checkClipboardButtons();
        });

        checkClipboardButtons();

        $form.on('submit', function (e) {
            //sprint_editor.deleteImagesBeforeSubmit();
            var resultString = saveToString();

            $editor.find('input,textarea,select').removeAttr('name');
            $inputresult.val(resultString);
        });

        if (params.enableChange) {

            packShow();

            $editor.on('click', '.sp-x-lt-col-copy', function (e) {
                e.preventDefault();
                var $grid = $(this).closest('.sp-x-lt');
                var $col = getActiveColumn($grid);

                $col.find('.sp-x-box').each(function () {
                    sprint_editor.copyToClipboard($(this).data('uid'));
                });

                popupToggle();
            });

            $editor.on('click', '.sp-x-lt-col-paste', function (e) {
                e.preventDefault();

                var clipboardData = sprint_editor.getClipboard();
                var $grid = $(this).closest('.sp-x-lt');
                var $col = getActiveColumn($grid);

                $.each(clipboardData, function (blockUid, blockData) {
                    blockAdd(blockData, $col);
                });

                sprint_editor.clearClipboard();
            });

            $editor.on('click', '.sp-x-pp-blocks .sp-x-btn', function (e) {
                addByName($(this));
            });

            $editor.on('click', '.sp-x-pp-main .sp-x-btn', function (e) {
                addByName($(this));
            });

            $editor.on('click', '.sp-x-pp-lt-open', function (e) {
                popupToggle($(this));
            });

            $editor.on('click', '.sp-x-pp-main-open', function (e) {
                popupToggle($(this));
            });
            $editor.on('click', '.sp-x-pp-box-open', function (e) {
                popupToggle($(this));
            });

            $editor.on('click', '.sp-x-pp-blocks-open', function (e) {
                popupToggle($(this));
            });

            $editor.on('click', '.sp-x-box-copy', function (e) {
                e.preventDefault();
                sprint_editor.copyToClipboard($(this).closest('.sp-x-box').data('uid'));
            });

            $editor.on('click', '.sp-x-box-up', function (e) {
                e.preventDefault();

                var $block = $(this).closest('.sp-x-box');
                var $grid = $(this).closest('.sp-x-lt');

                var $nblock = $block.prev('.sp-x-box');
                if ($nblock.length > 0) {
                    $block.insertBefore($nblock);
                } else {
                    var $ngrid = $grid.prev('.sp-x-lt');
                    if ($ngrid.length > 0) {
                        var $ncol = getActiveColumn($ngrid);
                        $block.appendTo($ncol);
                    }
                }
            });

            $editor.on('click', '.sp-x-box-dn', function (e) {
                e.preventDefault();

                var $block = $(this).closest('.sp-x-box');
                var $grid = $(this).closest('.sp-x-lt');

                var $nblock = $block.next('.sp-x-box');
                if ($nblock.length > 0) {
                    $block.insertAfter($nblock);
                } else {
                    var $ngrid = $grid.next('.sp-x-lt');
                    if ($ngrid.length > 0) {
                        var $ncol = getActiveColumn($ngrid);
                        var $head = $ncol.find('.sp-x-lt-settings');
                        if ($head.length > 0) {
                            $ncol = $head;
                        }
                        $block.insertAfter($ncol);
                    }
                }
            });

            $editor.on('click', '.sp-x-lt-up', function (e) {
                e.preventDefault();
                var block = $(this).closest('.sp-x-lt');
                var nblock = block.prev('.sp-x-lt');
                if (nblock.length > 0) {
                    block.insertBefore(nblock);
                }
            });

            $editor.on('click', '.sp-x-lt-dn', function (e) {
                e.preventDefault();
                var block = $(this).closest('.sp-x-lt');
                var nblock = block.next('.sp-x-lt');
                if (nblock.length > 0) {
                    block.insertAfter(nblock);
                }
            });

            $editor.on('click', '.sp-x-lt-col-left', function (e) {
                e.preventDefault();
                var $grid = $(this).closest('.sp-x-lt');
                var $tab = getActiveTab($grid);
                var $ntab = $tab.prev('.sp-x-lt-col-tab');
                if ($ntab.length > 0) {
                    $tab.insertBefore($ntab);
                }
            });

            $editor.on('click', '.sp-x-lt-col-right', function (e) {
                e.preventDefault();
                var $grid = $(this).closest('.sp-x-lt');
                var $tab = getActiveTab($grid);
                var $ntab = $tab.next('.sp-x-lt-col-tab');
                if ($ntab.length > 0) {
                    $tab.insertAfter($ntab);
                }
            });

            $editor.on('click', '.sp-x-box-del', function (e) {
                e.preventDefault();
                var $target = $(this).closest('.sp-x-box');

                var uid = $target.data('uid');
                sprint_editor.beforeDelete(uid);

                $target.hide(250, function () {
                    $target.remove();
                });
            });

            $editor.on('click', '.sp-x-lt-del', function (e) {
                e.preventDefault();

                var $grid = $(this).closest('.sp-x-lt');

                $grid.find('.sp-x-box').each(function () {
                    var uid = $(this).data('uid');
                    sprint_editor.beforeDelete(uid);
                });

                $grid.remove();

                popupToggle();
            });

            $editor.on('click', '.sp-x-lt-col-del', function (e) {
                e.preventDefault();

                var $grid = $(this).closest('.sp-x-lt');
                var $col = getActiveColumn($grid);

                var $tab = getActiveTab($grid);

                var columnUid = $col.data('uid');

                if ($col.length <= 0 || !columnUid) {
                    return false;
                }

                $col.find('.sp-x-box').each(function () {
                    var uid = $(this).data('uid');
                    sprint_editor.beforeDelete(uid);
                });


                var newxtindex = $grid.find('.sp-x-lt-col').index($col);

                var colcount = $grid.find('.sp-x-lt-col').length;
                var newcount = colcount - 1;
                if (newcount > 0) {

                    $tab.remove();
                    $col.remove();

                    var $newcol = $grid.find('.sp-x-lt-col').eq(newxtindex);
                    if ($newcol.length <= 0) {
                        $newcol = $grid.find('.sp-x-lt-col').last();
                    }

                    selectColumn(
                        $newcol.data('uid')
                    );

                    updateIndexes($grid);

                } else {
                    $grid.remove();
                }

                popupToggle();
            });

            $editor.on('click', '.sp-x-lt-col-add', function (e) {
                e.preventDefault();
                var $grid = $(this).closest('.sp-x-lt');

                var newcount = $grid.find('.sp-x-lt-col').length + 1;

                if (newcount > 4) {
                    return;
                }

                var ltname = 'type' + newcount;

                var columnUid = sprint_editor.makeUid();

                var html = sprint_editor.renderTemplate('box-layout-col', {
                    enableChange: params.enableChange,
                    showSortButtons: params.showSortButtons,
                    showCopyButtons: params.showCopyButtons,
                    title: BX.message('SPRINT_EDITOR_col_default'),
                    uid: columnUid,
                    compiledHtml: sprint_editor.renderTemplate('box-layout-col-settings', {
                        compiled: compileClasses(ltname, '')
                    })
                });

                var tab = sprint_editor.renderTemplate('box-layout-col-tab', {
                    uid: columnUid,
                    title: BX.message('SPRINT_EDITOR_col_default')
                });

                $grid.find('.sp-x-lt-tabs').append(tab);
                $grid.find('.sp-x-lt-row').append(html);

                $grid.find('.sp-x-lt-col').last().sortable({
                    items: ".sp-x-box",
                    connectWith: ".sp-x-lt-col",
                    handle: ".sp-x-box-handle",
                    placeholder: "sp-x-box-placeholder"
                });

                checkClipboardButtons();

                updateIndexes($grid);
            });

            $editor.on('click', '.sp-x-lt-col-edit', function (e) {
                var $grid = $(this).closest('.sp-x-lt');
                var $title = getActiveTab($grid).find('.sp-x-lt-col-title');
                layoutEditColumnTitle($title);

            });
        }

        $editor.on('click', '.sp-x-lt-col-tab', function (e) {
            selectColumn($(this).data('uid'));
        });

        $editor.on('click', '.sp-x-box-settings span', function (e) {
            var $span = $(this);

            $span.siblings('span').removeClass('sp-active');

            if ($span.hasClass('sp-active')) {
                $span.removeClass('sp-active');
            } else {
                $span.addClass('sp-active');
            }
        });

        $editor.on('click', '.sp-x-lt-settings span', function (e) {
            var $span = $(this);

            $span.siblings('span').removeClass('sp-active');

            if ($span.hasClass('sp-active')) {
                $span.removeClass('sp-active');
            } else {
                $span.addClass('sp-active');
            }
        });

        function scrollTo($elem) {
            $(document).scrollTop($elem.offset().top - 80);
        }

        function popupToggle($handler) {

            if (!$handler) {
                $editor.find('.sp-x-pp-box').hide();
                $editor.find('.sp-x-pp-lt').hide();
                $editor.find('.sp-x-pp-blocks').hide();
                $editor.find('.sp-x-pp-main').hide();
                $editor.find('.sp-x-pp-box-open').removeClass('sp-active');
                $editor.find('.sp-x-pp-lt-open').removeClass('sp-active');
                $editor.find('.sp-x-pp-blocks-open').removeClass('sp-active');
                $editor.find('.sp-x-pp-main-open').removeClass('sp-active');

                return true;
            }

            var $popup;

            if ($handler.hasClass('sp-x-pp-lt-open')) {
                $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-lt');
            }

            if ($handler.hasClass('sp-x-pp-box-open')) {
                $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-box');
            }

            if ($handler.hasClass('sp-x-pp-main-open')) {
                $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-main');
            }

            if ($handler.hasClass('sp-x-pp-blocks-open')) {
                $popup = $editor.find('.sp-x-pp-blocks');
                if (!$popup || $popup.length <= 0) {
                    $popup = $(sprint_editor.renderTemplate('pp-blocks' + params.uniqid, {}));
                }

                $handler.after($popup);
            }

            if ($handler.hasClass('sp-active')) {
                $handler.removeClass('sp-active');
                $popup.hide();

            } else {
                $editor.find('.sp-x-pp-box').hide();
                $editor.find('.sp-x-pp-lt').hide();
                $editor.find('.sp-x-pp-blocks').hide();
                $editor.find('.sp-x-pp-main').hide();
                $editor.find('.sp-x-pp-box-open').removeClass('sp-active');
                $editor.find('.sp-x-pp-lt-open').removeClass('sp-active');
                $editor.find('.sp-x-pp-blocks-open').removeClass('sp-active');
                $editor.find('.sp-x-pp-main-open').removeClass('sp-active');

                $handler.addClass('sp-active');
                $popup.show();
            }


        }

        function addByName($handler) {

            var name = $handler.data('name');

            if (name.indexOf('layout_') === 0) {
                name = name.substr(7);
                layoutEmptyAdd(name);
                popupToggle();

            } else if (name.indexOf('pack_') === 0) {
                name = name.substr(5);
                packLoad(name);
                popupToggle();

            } else if (name === 'delete_pack') {
                var packname = $handler.data('pack');
                if (packname.indexOf('pack_') === 0) {
                    packname = packname.substr(5);

                    if (confirm(BX.message('SPRINT_EDITOR_pack_del_confirm'))) {
                        packDelete(packname);
                    }

                } else {
                    alert(BX.message('SPRINT_EDITOR_pack_del_error'))
                }

            } else if (name === 'save_pack') {
                var packname = prompt(BX.message('SPRINT_EDITOR_pack_change'));
                if (packname) {
                    packSave('' + packname);
                }

            } else if (name) {
                var $grid = $handler.closest('.sp-x-lt');
                var $col = getActiveColumn($grid);
                if ($col.length > 0) {
                    blockAdd({name: name}, $col);
                }

                popupToggle();
            }

        }

        function checkClipboardButtons() {
            var clipboardData = sprint_editor.getClipboard();

            var cntBlocks = 0;
            $editor.find('.sp-x-box-copy').removeClass('sp-active');

            $.each(clipboardData, function (blockUid, blockData) {
                var $block = $editor.find('[data-uid=' + blockUid + ']');
                if ($block.length > 0) {
                    $block.find('.sp-x-box-copy').addClass('sp-active');
                }
                cntBlocks++;
            });

            if (cntBlocks > 0) {
                $editor.find('.sp-x-lt-col-paste').show();
            } else {
                $editor.find('.sp-x-lt-col-paste').hide();
            }
        }

        function layoutEmptyAdd(colCnt) {
            var ltname = 'type' + colCnt;

            var columns = [];
            var defaultclass = '';

            if (params.jsonUserSettings && params.jsonUserSettings.layout_defaults) {
                if (params.jsonUserSettings.layout_defaults[ltname]) {
                    defaultclass = params.jsonUserSettings.layout_defaults[ltname];
                }
            }

            for (var index = 1; index <= colCnt; index++) {
                columns.push({
                    css: defaultclass
                })
            }

            layoutAdd({
                columns: columns
            });
        }

        function layoutAdd(layout) {
            var ltname = 'type' + layout.columns.length;

            var columns = [];

            var firstUid = '';

            var layoutTitle = (layout.title) ? layout.title : BX.message('SPRINT_EDITOR_lt_default');

            $.each(layout.columns, function (index, column) {
                var columnUid = sprint_editor.makeUid();

                if (!firstUid) {
                    firstUid = columnUid;
                }

                var columnTitle = (column.title) ? column.title : BX.message('SPRINT_EDITOR_col_default');


                columns.push({
                    uid: columnUid,
                    html: sprint_editor.renderTemplate('box-layout-col', {
                        enableChange: params.enableChange,
                        showSortButtons: params.showSortButtons,
                        showCopyButtons: params.showCopyButtons,
                        title: columnTitle,
                        uid: columnUid,
                        compiledHtml: sprint_editor.renderTemplate('box-layout-col-settings', {
                            compiled: compileClasses(ltname, column.css)
                        })
                    }),
                    tab: sprint_editor.renderTemplate('box-layout-col-tab', {
                        uid: columnUid,
                        title: columnTitle
                    })
                })
            });

            $editor.find('.sp-x-editor-lt').append(
                sprint_editor.renderTemplate('box-layout', {
                    enableChange: params.enableChange,
                    showSortButtons: params.showSortButtons,
                    showCopyButtons: params.showCopyButtons,
                    columns: columns,
                    title: layoutTitle
                })
            );

            var $grid = $editor.find('.sp-x-lt').last();

            if (params.enableChange) {
                $grid.find('.sp-x-lt-col').sortable({
                    items: ".sp-x-box",
                    connectWith: ".sp-x-lt-col",
                    handle: ".sp-x-box-handle",
                    placeholder: "sp-x-box-placeholder"
                });
            }

            selectColumn(firstUid);

            checkClipboardButtons();

            updateIndexes($grid);
        }


        function blockAdd(blockData, $column) {
            if (!blockData || !blockData.name) {
                return false;
            }

            if (!sprint_editor.hasBlockParams(blockData.name)) {
                return false;
            }

            var uid = sprint_editor.makeUid();
            var blockSettings = sprint_editor.getBlockSettings(blockData.name, params);
            var html = sprint_editor.renderBlock(blockData, blockSettings, uid, params);

            if (!$column || $column.length <= 0) {
                if (blockData.layout) {
                    var pos = blockData.layout.split(',');
                    var $grid = $editor.find('.sp-x-lt').eq(pos[0]);
                    $column = $grid.find('.sp-x-lt-col').eq(pos[1]);
                }
            }

            if (!$column || $column.length <= 0) {
                return false;
            }

            $column.append(html);

            var $el = $column.find('.sp-x-box-block').last();
            var entry = sprint_editor.initblock($, $el, blockData.name, blockData, blockSettings);

            sprint_editor.initblockAreas($, $el, entry);
            sprint_editor._entries[uid] = entry;

            // scrollTo($el);
        }

        function packSave(packname) {
            $.post('/bitrix/admin/sprint.editor/assets/backend/pack.php', {
                save: saveToString(packname)
            }, function (resp) {
                if (resp) {
                    $editor.find('.sp-x-packs-loader').html(
                        sprint_editor.renderTemplate('box-select-pack', resp)
                    );
                }
            });
        }

        function packShow() {
            $.post('/bitrix/admin/sprint.editor/assets/backend/pack.php', {
                show: 1
            }, function (resp) {
                if (resp) {
                    $editor.find('.sp-x-packs-loader').html(
                        sprint_editor.renderTemplate('box-select-pack', resp)
                    );
                }
            });
        }

        function packDelete(packname) {
            $.post('/bitrix/admin/sprint.editor/assets/backend/pack.php', {
                del: packname
            }, function (resp) {
                if (resp) {
                    $editor.find('.sp-x-packs-loader').html(
                        sprint_editor.renderTemplate('box-select-pack', resp)
                    );
                }
            });
        }

        function packLoad(packname) {
            $.get('/bitrix/admin/sprint.editor/assets/backend/pack.php', {
                load: packname
            }, function (pack) {

                if (!pack || !pack.layouts || !pack.blocks) {
                    return;
                }

                var layoutIndex = layoutCnt();

                $.each(pack.layouts, function (index, layout) {
                    layoutAdd(layout)
                });

                $.each(pack.blocks, function (index, block) {
                    var pos = block.layout;

                    pos = pos.split(',');

                    pos = [
                        parseInt(pos[0], 10) + layoutIndex,
                        parseInt(pos[1], 10)
                    ];

                    var newblock = $.extend({}, block, {
                        layout: pos.join(',')
                    });

                    blockAdd(newblock);
                });

            });


        }

        function layoutCnt() {
            return $editor.find('.sp-x-lt').length;
        }

        function getActiveColumn($grid) {
            return $grid.find('.sp-x-lt-col.sp-active');
        }

        function getActiveTab($grid) {
            return $grid.find('.sp-x-lt-col-tab.sp-active');
        }

        function getActiveColumnUid($grid) {
            var $column = getActiveColumn($grid);
            return $column.data('uid');
        }

        function getColumnTab(columnUid) {
            return $editor.find('.sp-x-lt-col-tab[data-uid=' + columnUid + ']');
        }

        function getColumn(columnUid) {
            return $editor.find('.sp-x-lt-col[data-uid=' + columnUid + ']');
        }

        function selectColumn(columnUid) {
            var $tab = getColumnTab(columnUid);
            var $column = getColumn(columnUid);

            if ($tab.length > 0) {
                $tab.siblings('.sp-x-lt-col-tab').removeClass('sp-active');
                $tab.addClass('sp-active');
            }
            if ($column.length > 0) {
                $column.siblings('.sp-x-lt-col').removeClass('sp-active').hide();
                $column.addClass('sp-active').show();
            }

        }

        function layoutEditTitle($title) {
            var newtitle = prompt(BX.message('SPRINT_EDITOR_lt_change'), $title.text());
            newtitle = $.trim(newtitle);

            if (newtitle) {
                $title.text(newtitle);
            }
        }

        function layoutEditColumnTitle($title) {
            var newtitle = prompt(BX.message('SPRINT_EDITOR_col_change'), $title.text());
            newtitle = $.trim(newtitle);

            if (newtitle) {
                $title.text(newtitle);
            }
        }

        function getClassTitle(cssname) {
            if (params.jsonUserSettings && params.jsonUserSettings.layout_titles) {
                if (params.jsonUserSettings.layout_titles[cssname]) {
                    if (params.jsonUserSettings.layout_titles[cssname].length > 0) {
                        return params.jsonUserSettings.layout_titles[cssname];
                    }
                }
            }

            return cssname;
        }

        function compileClasses(ltname, cssstr) {

            var selectedCss = cssstr.split(' ');

            var allclasses = {};
            if (params.jsonUserSettings && params.jsonUserSettings.layout_classes) {
                if (params.jsonUserSettings.layout_classes[ltname]) {
                    if (params.jsonUserSettings.layout_classes[ltname].length > 0) {
                        allclasses = params.jsonUserSettings.layout_classes[ltname]
                    }
                }
            }

            var compiled = [];

            if (!allclasses) {
                return compiled;
            }

            $.each(allclasses, function (groupIndex, groupClasses) {

                if (!$.isArray(groupClasses)) {
                    return true;
                }

                var value = [];
                var valSel = 0;

                $.each(groupClasses, function (cssIndex, cssName) {

                    valSel = (
                        $.inArray(cssName, selectedCss) >= 0
                    ) ? 1 : 0;

                    value.push({
                        title: getClassTitle(cssName),
                        value: cssName,
                        selected: valSel
                    })
                });


                compiled.push({
                    options: value
                })
            });

            return compiled;
        }

        function saveToString(packname) {
            packname = packname || '';

            var blocks = [];
            var layouts = [];

            var index = 0;

            $editor.find('.sp-x-lt').each(function (gindex) {
                var columns = [];

                // var lttitle = $(this).find('.sp-x-lt-title').text();
                // var lttitle = BX.message('SPRINT_EDITOR_lt_default');

                $(this).find('.sp-x-lt-col-tab').each(function (cindex) {
                    var $tab = $(this);

                    var columnUid = $tab.data('uid');

                    var $col = getColumn(columnUid);

                    var $title = $tab.find('.sp-x-lt-col-title');
                    var coltitle = $title.text();

                    var colclasses = [];
                    $col.find('.sp-x-lt-settings .sp-active').each(function () {
                        var cssname = $(this).data('value');
                        colclasses.push(
                            $.trim(cssname)
                        );
                    });

                    if (coltitle !== BX.message('SPRINT_EDITOR_col_default')) {
                        columns.push({
                            title: coltitle,
                            css: colclasses.join(' ')
                        });
                    } else {
                        columns.push({
                            css: colclasses.join(' ')
                        });
                    }

                    $col.find('.sp-x-box').each(function () {

                        var uid = $(this).data('uid');

                        if (!sprint_editor._entries[uid]) {
                            return true;
                        }

                        var blockData = sprint_editor.collectData(uid);

                        var settcnt = 0;
                        var settval = {};
                        var $boxsett = $(this).find('.sp-x-box-settings');
                        $boxsett.find('.sp-x-box-settings-group').each(function () {
                            var name = $(this).data('name');
                            var $val = $(this).find('.sp-active').first();

                            if ($val.length > 0) {
                                settval[name] = $val.data('value');
                                settcnt++;
                            }
                        });

                        if (settcnt > 0) {
                            blockData.settings = settval;
                        } else {
                            delete blockData.settings;
                        }

                        blockData.layout = gindex + ',' + cindex;
                        blocks.push(blockData);
                        index++;

                    });

                });

                if (columns.length > 0) {

                    layouts.push({
                        columns: columns
                    });

                }

            });

            var resultString = '';

            if (layouts.length > 0) {
                var post = {
                    packname: packname,
                    version: 2,
                    blocks: blocks,
                    layouts: layouts
                };

                resultString = JSON.stringify(post);
                resultString = resultString.replace(/\\n/g, "\\n")
                    .replace(/\\'/g, "\\'")
                    .replace(/\\"/g, '\\"')
                    .replace(/\\&/g, "\\&")
                    .replace(/\\r/g, "\\r")
                    .replace(/\\t/g, "\\t")
                    .replace(/\\b/g, "\\b")
                    .replace(/\\f/g, "\\f");
            }

            return resultString;
        }

        function updateIndexes($grid) {

            $grid.find('.sp-x-lt-col-index').each(function (cindex) {
                $(this).text(cindex + 1);
            });

        }
    }
};



