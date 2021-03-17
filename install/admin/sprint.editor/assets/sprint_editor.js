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
    _clipboardUid: 'sprint-editor-cb02',

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

    registerEntry: function (uid, entry) {
        this._entries[uid] = entry;
    },

    hasEntry: function (uid) {
        return !!this._entries[uid];
    },

    getEntry: function (uid) {
        return (this._entries[uid]) ? this._entries[uid] : false;
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

    getBlockTitle: function (name) {
        var params = this.getBlockParams(name);
        if (params && params['title']) {
            return params['title'];
        }
        return '';
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

    renderString: function(str, data) {
        var func = window.doT.template(str);
        return func(data);
    },

    markImagesForDelete: function (items) {
        this._imagesdelete = jQuery.extend({}, this._imagesdelete, items);
    },

    initblock: function ($, $el, name, blockData, blockSettings, currentEditorParams) {
        name = sprint_editor.hasBlockMethod(name) ? name : 'dump';

        var method = sprint_editor.getBlockMethod(name);
        var entry = new method(
            $,
            $el,
            blockData,
            blockSettings,
            currentEditorParams
        );

        var html = sprint_editor.renderTemplate(name, entry.getData());
        $el.html(html).addClass('sp-block-' + name);

        if (typeof entry.afterRender == 'function') {
            entry.afterRender();
        }

        return entry;
    },

    initblockAreas: function ($, $el, entry, currentEditorParams) {
        if (entry && typeof entry.getAreas == 'function') {
            var areas = entry.getAreas();
            var entryData = entry.getData();
            for (var prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    var area = areas[prop];
                    area.blockSettings = area.blockSettings || {};

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
                        areaData,
                        area.blockSettings,
                        currentEditorParams
                    );
                }
            }
        }
    },

    beforeDelete: function (uid) {
        if (!sprint_editor.hasEntry(uid)) {
            return;
        }

        var entry = sprint_editor.getEntry(uid);

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
    afterSort: function (uid) {
        if (!sprint_editor.hasEntry(uid)) {
            return;
        }

        var entry = sprint_editor.getEntry(uid);

        if (typeof entry.afterSort === 'function') {
            entry.afterSort();
        }

        if (typeof entry.getAreas === 'function') {
            var areas = entry.getAreas();
            for (var prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    var area = areas[prop];
                    if (typeof area.block.afterSort === 'function') {
                        area.block.afterSort();
                    }
                }
            }
        }
    },
    collectData: function (uid) {
        var blockData = {};
        if (!sprint_editor.hasEntry(uid)) {
            return blockData;
        }

        var entry = sprint_editor.getEntry(uid);

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

    copyToClipboard: function (uid, deleteAfterPaste) {
        if (window.localStorage && sprint_editor.hasEntry(uid)) {
            var val = JSON.parse(
                localStorage.getItem(
                    sprint_editor._clipboardUid
                )
            );

            val = (val) ? val : {};
            deleteAfterPaste = !!deleteAfterPaste;

            if (val[uid] && val[uid].deleteAfterPaste === deleteAfterPaste) {
                delete val[uid];
            } else {
                val[uid] = {
                    block: sprint_editor.collectData(uid),
                    deleteAfterPaste: deleteAfterPaste
                };
            }

            localStorage.setItem(
                sprint_editor._clipboardUid,
                JSON.stringify(val)
            );
            this.fireEvent('clipboard:change');
        }
    },

    clearClipboard: function () {
        if (window.localStorage) {
            localStorage.removeItem(
                sprint_editor._clipboardUid
            );
            this.fireEvent('clipboard:change');
        }
    },

    getClipboard: function () {
        var val = {};
        if (window.localStorage) {
            val = JSON.parse(
                localStorage.getItem(
                    sprint_editor._clipboardUid
                )
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
        if (renderVars) {

            renderVars['uid'] = uid;
            renderVars['enableChange'] = params.enableChange;
            renderVars['compiled'] = sprint_editor.compileSettings(blockData, blockSettings);

            return this.renderTemplate('box', renderVars);
        }
        return '';
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
            var nothingSelected = true;

            jQuery.each(setSet.value, function (valVal, valTitle) {
                var isSelected = (
                    blockData.settings &&
                    blockData.settings[setName] &&
                    blockData.settings[setName] == valVal
                ) ? 1 : 0;

                if (isSelected) {
                    nothingSelected = false;
                }

                value.push({
                    title: valTitle,
                    value: valVal,
                    selected: isSelected
                })
            });

            if (nothingSelected && setSet.hasOwnProperty('default')) {
                jQuery.each(value, function (index, valItem) {
                    if (valItem.value == setSet.default) {
                        valItem.selected = 1;
                    }
                });
            }

            compiled.push({
                name: setName,
                options: value
            })

        });

        return compiled;
    },
    compileClasses: function (ltname, cssstr, params) {

        var selectedCss = cssstr.split(' ');

        var allclasses = {};
        if (params.jsonUserSettings.layout_classes) {
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

        jQuery.each(allclasses, function (groupIndex, groupClasses) {

            if (!jQuery.isArray(groupClasses)) {
                return true;
            }

            var value = [];
            var valSel = 0;

            jQuery.each(groupClasses, function (cssIndex, cssName) {

                valSel = (
                    jQuery.inArray(cssName, selectedCss) >= 0
                ) ? 1 : 0;

                value.push({
                    title: sprint_editor.getClassTitle(cssName, params),
                    value: cssName,
                    selected: valSel
                })
            });


            compiled.push({
                options: value
            })
        });

        return compiled;
    },
    getClassTitle: function (cssname, params) {
        if (params.jsonUserSettings.layout_titles) {
            if (params.jsonUserSettings.layout_titles[cssname]) {
                if (params.jsonUserSettings.layout_titles[cssname].length > 0) {
                    return params.jsonUserSettings.layout_titles[cssname];
                }
            }
        }

        return cssname;
    },

    getBlockSettings: function (name, params) {
        var blockSettings = {};

        if (!params.hasOwnProperty('jsonUserSettings')) {
            return blockSettings;
        }

        if (!params.jsonUserSettings.hasOwnProperty('block_settings')) {
            return blockSettings;
        }

        if (!params.jsonUserSettings.block_settings.hasOwnProperty(name)) {
            return blockSettings;
        }

        return params.jsonUserSettings.block_settings[name];

    },

    getLayoutSettings: function (name, params) {
        var layoutSettings = {};

        if (!params.hasOwnProperty('jsonUserSettings')) {
            return layoutSettings;
        }

        if (!params.jsonUserSettings.hasOwnProperty('layout_settings')) {
            return layoutSettings;
        }

        if (!params.jsonUserSettings.layout_settings.hasOwnProperty(name)) {
            return layoutSettings;
        }

        return params.jsonUserSettings.layout_settings[name];

    },

    safeStringify: function (data) {
        data = JSON.stringify(data);

        data = data.replace(/\\n/g, "\\n")
            .replace(/\\'/g, "\\'")
            .replace(/\\"/g, '\\"')
            .replace(/\\&/g, "\\&")
            .replace(/\\r/g, "\\r")
            .replace(/\\t/g, "\\t")
            .replace(/\\b/g, "\\b")
            .replace(/\\f/g, "\\f")
            .replace(/([\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|\uD83E[\uDD10-\uDDFF])/g, '');

        return data;
    },
    collectSettings($settings) {
        var settcnt = 0;
        var settval = {};

        $settings.find('.sp-x-settings-group').each(function () {
            var name = $(this).data('name');
            var $val = $(this).find('.sp-x-active').first();

            if ($val.length > 0) {
                settval[name] = $val.data('value');
                settcnt++;
            }
        });

        return settval;
    }
};
