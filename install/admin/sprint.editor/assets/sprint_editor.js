var sprint_editor = {
    _templates: {},
    _parameters: {},
    _tplcache: {},
    _registered: {},
    _events: {},
    _entries: {},

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
        return '/bitrix/admin/sprint.editor/' + values.groupname + '/' + values.name;
    },

    renderTemplate: function (name, data) {
        if (window.doT) {
            name = (this._templates[name]) ? name : 'dump';
            if (!this._tplcache[name]) {
                this._tplcache[name] = window.doT.template(this._templates[name]);
            }

            var tempfn = this._tplcache[name];
            return tempfn(data);
        } else {
            return '';
        }
    },

    initblock: function ($el, name, blockData) {
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

    initblockAreas: function ($el, entry) {
        if (entry && typeof entry.getAreas == 'function') {
            var areas = entry.getAreas();
            var entryData = entry.getData();

            for (var prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    var area = areas[prop];
                    if (entryData.name != area.blockName) {
                        area.block = sprint_editor.initblock($el.find(area.container), area.blockName, entryData[area.dataKey]);
                    }
                }
            }
        }
    },

    collectData: function (entry) {
        var blockData = {};

        if (entry && typeof entry.collectData == 'function') {
            blockData = entry.collectData();

            if (typeof entry.getAreas === 'function') {
                var areas = entry.getAreas();

                for (var prop in areas) {
                    if (areas.hasOwnProperty(prop)) {
                        var area = areas[prop];
                        blockData[area.dataKey] = area.block.collectData();
                    }
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

    getClipboardCnt: function () {
        var val = [];
        if (window.localStorage) {
            val = JSON.parse(
                localStorage.getItem('sprint-editor-copyblocks')
            )
        }

        return (val && val.length > 0) ? val.length : 0;
    },

    copyToClipboard: function (blockData) {
        if (window.localStorage) {
            var val = JSON.parse(
                localStorage.getItem('sprint-editor-copyblocks')
            );

            val = (val) ? val : [];
            
            delete blockData.layout;

            val.push(blockData);
            localStorage.setItem("sprint-editor-copyblocks", JSON.stringify(val));
            this.fireEvent('copy');
        }
    },

    clearClipboard: function () {
        if (window.localStorage) {
            localStorage.removeItem('sprint-editor-copyblocks');
            this.fireEvent('copy');
        }

    },
    getClipboard: function () {
        var val = [];
        if (window.localStorage) {
            val = JSON.parse(
                localStorage.getItem('sprint-editor-copyblocks')
            );
        }

        return val;

    },

    makeUid: function () {
        return 'sp-x-' + Math.random().toString(36).substring(2, 12);
    },

    create: function ($, params) {
        var $editor = $('.sp-x-editor' + params.uniqid);
        var $inputresult = $('.sp-x-result' + params.uniqid);
        var $form = $editor.closest('form').first();

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


        checkClipboardButtons();
        checkLayoutButtons();

        $form.on('submit', function (e) {

            var blocks = [];
            var layouts = [];

            var index = 0;

            $editor.find('.sp-x-lt-table').each(function (pos1) {
                var columns = [];

                $(this).find('.sp-x-lt-col').each(function (pos2) {

                    var text = $(this).find('.sp-x-lt-curtype').text();
                    columns.push(text);

                    $(this).find('.sp-x-box').each(function () {

                        var uid = $(this).data('uid');

                        if (!sprint_editor._entries[uid]) {
                            return true;
                        }

                        var blockData = sprint_editor.collectData(
                            sprint_editor._entries[uid]
                        );



                        var $boxsett = $(this).find('.sp-x-box-settings');
                        var settarr = $boxsett.find("select,input").serializeArray();

                        delete blockData.settings;
                        if (settarr.length > 0){
                            blockData.settings = {};
                            $.each(settarr, function () {
                                blockData.settings[this.name] = this.value;
                            });
                        }

                        blockData.layout = pos1 + ',' + pos2;
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
                    $editor.removeClass('sp-x-layout-mode');
                } else {
                    $editor.addClass('sp-x-layout-mode');
                }
            });

            $editor.on('click', '.sp-x-lt-copy', function (e) {
                e.preventDefault();
                var $col = $(this).closest('.sp-x-lt-table');
                $col.find('.sp-x-box').each(function () {
                    var uid = $(this).data('uid');
                    if (sprint_editor._entries[uid]) {
                        sprint_editor.copyToClipboard(
                            sprint_editor.collectData(sprint_editor._entries[uid])
                        );
                    }
                });
            });

            $editor.on('click', '.sp-x-box-copy', function (e) {
                e.preventDefault();
                var uid = $(this).closest('.sp-x-box').data('uid');
                if (sprint_editor._entries[uid]) {
                    sprint_editor.copyToClipboard(
                        sprint_editor.collectData(sprint_editor._entries[uid])
                    );
                }
            });

            $editor.on('click', '.sp-x-box-paste', function (e) {
                e.preventDefault();

                var clipboardData = sprint_editor.getClipboard();

                $.each(clipboardData, function (index, blockData) {
                    blockAdd(blockData);
                });

                sprint_editor.clearClipboard();
                checkLayoutButtons();
            });

            $editor.on('click', '.sp-x-box-add', function (e) {
                var name = $editor.find('.sp-x-box-select').val();
                if (name.indexOf('layout_') === 0) {
                    name = name.substr(7);
                    layoutEmptyAdd(name);
                } else {
                    blockAdd({name: name});
                }
                checkLayoutButtons();
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

            $editor.on('click', '.sp-x-box-del', function (e) {
                e.preventDefault();
                $(this).closest('.sp-x-box').remove();
            });

            $editor.on('click', '.sp-x-lt-del', function (e) {
                e.preventDefault();
                $(this).closest('.sp-x-lt-table').remove();
                checkLayoutButtons();
            });

            $editor.on('click', '.sp-x-lt-types span', function (e) {
                var $span = $(this);

                var $xcol = $span.closest('.sp-x-lt-col');
                var $cursize = $xcol.find('.sp-x-lt-curtype');
                var $sizes = $xcol.find('.sp-x-lt-types');

                $span.siblings('span').removeClass('active');

                if ($span.hasClass('active')) {
                    $span.removeClass('active');
                } else {
                    $span.addClass('active');
                }

                var result = [];
                $sizes.find('.active').each(function () {
                    var tmp = $(this).text();
                    tmp = $.trim(tmp);
                    result.push(tmp);
                });

                $cursize.text(result.join(' '));

            });

            $editor.on('click', '.sp-x-lt-title', function (e) {
                var $title = $(this);
                var $xcol = $title.closest('.sp-x-lt-col');
                var $sizes = $xcol.find('.sp-x-lt-types');
                if ($sizes.length > 0) {
                    if ($title.hasClass('active')) {
                        $sizes.hide();
                        $title.removeClass('active');
                    } else {
                        $editor.find('.sp-x-lt-types').not($sizes).hide();
                        $editor.find('.sp-x-lt-title').not($title).removeClass('active');

                        var cursizes = $xcol.find('.sp-x-lt-curtype').text();
                        cursizes = cursizes.split(' ');
                        $sizes.find('span').each(function () {
                            var stext = $(this).text();
                            stext = $.trim(stext);
                            if (stext && $.inArray(stext, cursizes) >= 0) {
                                $(this).addClass('active');
                            }
                        });

                        $sizes.show();
                        $title.addClass('active');
                    }
                }
            });
        }

        function checkClipboardButtons() {
            var cnt = sprint_editor.getClipboardCnt();
            if (cnt > 0) {
                var $btn = $editor.find('.sp-x-box-paste');
                $btn.val(
                    $btn.data('title') + ' (' + cnt + ')'
                ).show();

            } else {
                $editor.find('.sp-x-box-paste').hide();
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
                columns: columns
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
                var $allcolls = $editor.find('.sp-x-lt-col');
                $allcolls.sortable({
                    items: ".sp-x-box",
                    connectWith: $allcolls,
                    handle: ".sp-x-box-handle",
                    placeholder: "sp-x-box-placeholder"
                });
            }

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

            var $column = $editor.find('.sp-x-lt-col').last();

            if (blockData.layout) {
                var pos = blockData.layout.split(',');
                $editor.find('.sp-x-lt-table').each(function (pos1) {
                    if (pos1 == pos[0]) {
                        $(this).find('.sp-x-lt-col').each(function (pos2) {
                            if (pos2 == pos[1]) {
                                $column = $(this);
                                return false;
                            }
                        });
                        return false;
                    }
                });
            }

            $column.append(html);

            var $el = $column.find('.sp-x-box-block').last();
            var entry = sprint_editor.initblock($el, blockData.name, blockData);
            sprint_editor.initblockAreas($el, entry);
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



