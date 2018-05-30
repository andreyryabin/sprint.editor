var sprint_editor = {
    _templates: {},
    _tplcache: {},
    _parameters: {},
    _registered: {},
    _events: {},
    _entries: {},
    _uidcounter: 0,

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
        if (window.doT && this._templates[name]) {
            if (this._tplcache[name]) {
                return this._tplcache[name](data);
            } else {
                this._tplcache[name] = window.doT.template(this._templates[name]);
                return this._tplcache[name](data);
            }
        } else {
            return '';
        }
    },

    initblock: function ($, $el, name, blockData) {
        name = sprint_editor.hasBlockMethod(name) ? name : 'dump';

        var method = sprint_editor.getBlockMethod(name);
        var entry = new method($, $el, blockData);

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
                    area.block = sprint_editor.initblock($, $el.find(area.container), area.blockName, entryData[area.dataKey]);
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
                blockData[area.dataKey] = area.block.collectData();
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

        prefix = (prefix) ? prefix : 'sp-x-';

        return prefix + this._uidcounter + uniq;
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

        $.each(params.jsonValue.layouts, function (index, columns) {
            layoutAdd(columns);
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


        checkLayoutButtons();
        checkClipboardButtons();

        $form.on('submit', function (e) {

            var blocks = [];
            var layouts = [];

            var index = 0;

            $editor.find('.sp-x-lt-grid').each(function (gindex) {
                var columns = [];

                $(this).find('.sp-x-lt-col').each(function (cindex) {

                    var text = $(this).find('.sp-x-lt-settings-current').text();
                    columns.push(text);

                    $(this).find('.sp-x-box').each(function () {

                        var uid = $(this).data('uid');

                        if (!sprint_editor._entries[uid]) {
                            return true;
                        }

                        var blockData = sprint_editor.collectData(uid);


                        var $boxsett = $(this).find('.sp-x-box-settings');
                        var settarr = $boxsett.find("select,input").serializeArray();

                        delete blockData.settings;
                        if (settarr.length > 0) {
                            blockData.settings = {};
                            $.each(settarr, function () {
                                blockData.settings[this.name] = this.value;
                            });
                        }

                        blockData.layout = gindex + ',' + cindex;
                        blocks.push(blockData);
                        index++;

                    });

                });

                layouts.push(columns);
            });

            var resultString = '';

            if (layouts.length > 0) {
                var post = {
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


            $editor.find('input,textarea,select').removeAttr('name');
            $inputresult.val(resultString);
        });

        if (params.enableChange) {

            $editor.on('click', '.sp-x-layout-toggle', function (e) {
                if ($editor.hasClass('sp-x-layout-mode')) {
                    $(this).removeClass('adm-btn-active');
                    $editor.removeClass('sp-x-layout-mode');
                } else {
                    $(this).addClass('adm-btn-active');
                    $editor.addClass('sp-x-layout-mode');
                }

                $(document).scrollTop($editor.offset().top - 80);
            });

            $editor.on('click', '.sp-x-lt-copy', function (e) {
                e.preventDefault();
                var $col = $(this).closest('.sp-x-lt-col');
                $col.find('.sp-x-box').each(function () {
                    sprint_editor.copyToClipboard($(this).data('uid'));
                });
            });

            $editor.on('click', '.sp-x-lt-paste', function (e) {
                e.preventDefault();

                var $grid = $(this).closest('.sp-x-lt-grid');
                var $col = $(this).closest('.sp-x-lt-col');

                var gindex = $editor.find('.sp-x-lt-grid').index($grid);
                var cindex = $grid.find('.sp-x-lt-col').index($col);

                var clipboardData = sprint_editor.getClipboard();

                $.each(clipboardData, function (blockUid, blockData) {
                    blockData.layout = gindex + ',' + cindex;
                    blockAdd(blockData);
                });

                sprint_editor.clearClipboard();
            });

            $editor.on('click', '.sp-x-box-copy', function (e) {
                e.preventDefault();
                sprint_editor.copyToClipboard($(this).closest('.sp-x-box').data('uid'));
            });

            $editor.on('click', '.sp-x-box-add', function (e) {
                var name = $editor.find('.sp-x-box-select').val();
                if (name.indexOf('layout_') === 0) {
                    name = name.substr(7);
                    layoutEmptyAdd(name);
                } else {
                    blockAdd({name: name});
                }
            });

            $editor.on('click', '.sp-x-box-up', function (e) {
                e.preventDefault();
                var block = $(this).closest('.sp-x-box');
                var nblock = block.prev('.sp-x-box');
                if (nblock.length > 0) {
                    block.insertBefore(nblock);
                }
            });

            $editor.on('click', '.sp-x-box-dn', function (e) {
                e.preventDefault();
                var block = $(this).closest('.sp-x-box');
                var nblock = block.next('.sp-x-box');
                if (nblock.length > 0) {
                    block.insertAfter(nblock);
                }
            });


            $editor.on('click', '.sp-x-grid-up', function (e) {
                e.preventDefault();
                var block = $(this).closest('.sp-x-lt-grid');
                var nblock = block.prev('.sp-x-lt-grid');
                if (nblock.length > 0) {
                    block.insertBefore(nblock);
                }
            });

            $editor.on('click', '.sp-x-grid-dn', function (e) {
                e.preventDefault();
                var block = $(this).closest('.sp-x-lt-grid');
                var nblock = block.next('.sp-x-lt-grid');
                if (nblock.length > 0) {
                    block.insertAfter(nblock);
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

            $editor.on('click', '.sp-x-grid-del', function (e) {
                e.preventDefault();

                var $grid = $(this).closest('.sp-x-lt-grid');

                $grid.find('.sp-x-box').each(function () {
                    var uid = $(this).data('uid');
                    sprint_editor.beforeDelete(uid);
                });

                $grid.hide(250, function () {
                    $grid.remove();
                });


            });

            $editor.on('click', '.sp-x-lt-del', function (e) {
                e.preventDefault();
                var $grid = $(this).closest('.sp-x-lt-grid');
                var $col = $(this).closest('.sp-x-lt-col');

                $col.find('.sp-x-box').each(function () {
                    var uid = $(this).data('uid');
                    sprint_editor.beforeDelete(uid);
                });

                var colcount = $grid.find('.sp-x-lt-col').length;
                var newcount = colcount - 1;
                if (newcount > 0) {
                    $col.hide(250, function () {
                        $col.remove();
                        $grid.attr('class', 'sp-x-lt-grid').addClass('sp-x-lt-type' + newcount);
                    });
                } else {
                    $grid.hide(250, function () {
                        $grid.remove();
                    });
                }


            });

            $editor.find('.sp-x-boxes').sortable({
                items: ".sp-x-lt-grid",
                handle: ".sp-x-grid-handle",
                placeholder: "sp-x-grid-placeholder"
            });

        }

        $editor.on('change', '.sp-x-box-settings select', function (e) {
            var $span = $(this);
            var $xcol = $span.closest('.sp-x-box');
            var result = [];

            var $sizes = $xcol.find('.sp-x-box-settings');
            $sizes.find('select').each(function () {
                var tmp = $(this).find('option:selected').text();
                result.push(tmp);

            });

            var $cursize = $xcol.find('.sp-x-box-settings-current');
            $cursize.text(result.join(' '));
        });

        $editor.on('click', '.sp-x-lt-settings span', function (e) {
            var $span = $(this);

            var $xcol = $span.closest('.sp-x-lt-col');
            var $cursize = $xcol.find('.sp-x-lt-settings-current');
            var $sizes = $xcol.find('.sp-x-lt-settings');

            $span.siblings('span').removeClass('sp-active');

            if ($span.hasClass('sp-active')) {
                $span.removeClass('sp-active');
            } else {
                $span.addClass('sp-active');
            }

            var result = [];
            $sizes.find('.sp-active').each(function () {
                var tmp = $(this).text();
                tmp = $.trim(tmp);
                result.push(tmp);
            });

            $cursize.text(result.join(' '));

        });

        $editor.on('click', '.sp-x-box-settings-toggle', function (e) {
            var $title = $(this);
            var $xcol = $title.closest('.sp-x-box');
            var $sizes = $xcol.find('.sp-x-box-settings');

            $editor.find('.sp-x-lt-settings').hide();
            $editor.find('.sp-x-box-settings').hide();
            $editor.find('.sp-x-lt-settings-toggle').removeClass('sp-active');
            $editor.find('.sp-x-box-settings-toggle').not($title).removeClass('sp-active');

            if ($title.hasClass('sp-active')) {
                $sizes.hide(250);
                $title.removeClass('sp-active');
            } else {
                $sizes.show(250);
                $title.addClass('sp-active');
            }
        });

        $editor.on('click', '.sp-x-lt-settings-toggle', function (e) {
            var $title = $(this);
            var $xcol = $title.closest('.sp-x-lt-col');
            var $sizes = $xcol.find('.sp-x-lt-settings');

            $editor.find('.sp-x-lt-settings').hide();
            $editor.find('.sp-x-box-settings').hide();
            $editor.find('.sp-x-lt-settings-toggle').not($title).removeClass('sp-active');
            $editor.find('.sp-x-box-settings-toggle').removeClass('sp-active');

            if ($sizes.length > 0) {
                if ($title.hasClass('sp-active')) {
                    $sizes.hide(250);
                    $title.removeClass('sp-active');
                } else {
                    $editor.find('.sp-x-lt-settings-toggle').not($title).removeClass('sp-active');

                    var cursizes = $xcol.find('.sp-x-lt-settings-current').text();
                    cursizes = cursizes.split(' ');
                    $sizes.find('span').each(function () {
                        var stext = $(this).text();
                        stext = $.trim(stext);
                        if (stext && $.inArray(stext, cursizes) >= 0) {
                            $(this).addClass('sp-active');
                        }
                    });

                    $sizes.show(250);
                    $title.addClass('sp-active');
                }
            }
        });


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
                $editor.find('.sp-x-lt-paste').show();
            } else {
                $editor.find('.sp-x-lt-paste').hide();
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
                columns.push(defaultclass)
            }

            layoutAdd(columns);
        }

        function layoutAdd(columns) {
            var ltname = 'type' + columns.length;
            var renderVars = {
                enableChange: params.enableChange,
                columns: columns,
                showSortButtons: params.showSortButtons
            };

            if (params.jsonUserSettings && params.jsonUserSettings.layout_classes) {
                if (params.jsonUserSettings.layout_classes[ltname]) {
                    if (params.jsonUserSettings.layout_classes[ltname].length > 0) {
                        renderVars.classes = params.jsonUserSettings.layout_classes[ltname]
                    }
                }
            }

            $editor.find('.sp-x-boxes').append(
                sprint_editor.renderTemplate('box-layout', renderVars)
            );

            if (params.enableChange) {
                var $allcolls = $editor.find('.sp-x-lt-dock');
                $allcolls.sortable({
                    items: ".sp-x-box",
                    connectWith: $allcolls,
                    handle: ".sp-x-box-handle",
                    placeholder: "sp-x-box-placeholder"
                });
            }

            checkLayoutButtons();
            checkClipboardButtons();
        }

        function checkLayoutButtons() {
            if ($editor.find('.sp-x-lt-col').length <= 0) {
                $editor.find('.sp-x-layout-toggle').hide();
            } else {
                $editor.find('.sp-x-layout-toggle').show();
            }
        }

        function blockAdd(blockData) {
            if (!blockData.name || !sprint_editor.hasBlockParams(blockData.name)) {
                return false;
            }

            var blockParams = sprint_editor.getBlockParams(blockData.name);
            blockParams.uid = sprint_editor.makeUid();
            blockParams.showSortButtons = params.showSortButtons;
            blockParams.enableChange = params.enableChange;

            blockParams.settings = {};
            if (params.jsonUserSettings && params.jsonUserSettings.block_settings) {
                if (params.jsonUserSettings.block_settings[blockData.name]) {
                    blockParams.settings = params.jsonUserSettings.block_settings[blockData.name];
                }
            }

            blockParams.compiled = compileSettings(blockParams, blockData);

            var html = sprint_editor.renderTemplate('box', blockParams);

            if ($editor.find('.sp-x-lt-col').length <= 0) {
                layoutEmptyAdd(1);
            }

            var $column = false;
            if (blockData.layout) {
                var pos = blockData.layout.split(',');
                var $grid = $editor.find('.sp-x-lt-grid').eq(pos[0]);
                $column = $grid.find('.sp-x-lt-col').eq(pos[1]);
            }

            if (!$column || $column.length <= 0) {
                $column = $editor.find('.sp-x-lt-col').last();
            }

            $column.find('.sp-x-lt-dock').append(html);

            var $el = $column.find('.sp-x-box-block').last();
            var entry = sprint_editor.initblock($, $el, blockData.name, blockData);
            sprint_editor.initblockAreas($, $el, entry);
            sprint_editor._entries[blockParams.uid] = entry;
        }

        function compileSettings(blockParams, blockData) {
            var compiled = [];

            if (!blockParams.settings) {
                return compiled;
            }

            $.each(blockParams.settings, function (setName, setSet) {

                if (!setSet.value || !setSet.type || setSet.type != 'select') {
                    return true;
                }

                var value = [];
                var valSel = 0;

                $.each(setSet.value, function (valVal, valTitle) {

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
                    value: value
                })
            });

            return compiled;
        }

    }
};



