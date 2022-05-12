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
        let params = this.getBlockParams(name);
        if (params && params['title']) {
            return params['title'];
        }
        return '';
    },

    getBlockWebPath: function (blockName) {
        let values = this.getBlockParams(blockName);
        if (values.islocal) {
            return '/local/admin/sprint.editor/' + values.groupname + '/' + values.name;
        } else {
            return '/bitrix/admin/sprint.editor/' + values.groupname + '/' + values.name;
        }
    },

    renderTemplate: function (name, data) {
        if (!this._tplcache[name]) {
            let $tpl = jQuery('#sp-x-template-' + name);
            if ($tpl.length > 0) {
                this._tplcache[name] = $tpl.html();
            } else {
                this._tplcache[name] = '';
            }
        }

        if (window.doT) {
            let func = window.doT.template(
                this._tplcache[name]
            );
            return func(data);
        }

        return '';
    },

    renderString: function (str, data) {
        let func = window.doT.template(str);
        return func(data);
    },

    markImagesForDelete: function (items) {
        this._imagesdelete = jQuery.extend({}, this._imagesdelete, items);
    },

    initblock: function ($, $el, name, blockData, blockSettings, currentEditorParams) {
        name = sprint_editor.hasBlockMethod(name) ? name : 'dump';

        let method = sprint_editor.getBlockMethod(name);
        let entry = new method(
            $,
            $el,
            blockData,
            blockSettings,
            currentEditorParams
        );

        let html = sprint_editor.renderTemplate(name, entry.getData());
        $el.html(html).addClass('sp-block-' + name);

        if (typeof entry.afterRender == 'function') {
            entry.afterRender();
        }

        return entry;
    },

    initblockAreas: function ($, $el, entry, currentEditorParams) {
        if (entry && typeof entry.getAreas == 'function') {
            let areas = entry.getAreas();
            let entryData = entry.getData();
            for (let prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    let area = areas[prop];
                    area.blockSettings = area.blockSettings || {};

                    let areaData = {};
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

        let entry = sprint_editor.getEntry(uid);

        if (typeof entry.beforeDelete === 'function') {
            entry.beforeDelete();
        }

        if (typeof entry.getAreas === 'function') {
            let areas = entry.getAreas();
            for (let prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    let area = areas[prop];
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

        let entry = sprint_editor.getEntry(uid);

        if (typeof entry.afterSort === 'function') {
            entry.afterSort();
        }

        if (typeof entry.getAreas === 'function') {
            let areas = entry.getAreas();
            for (let prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    let area = areas[prop];
                    if (typeof area.block.afterSort === 'function') {
                        area.block.afterSort();
                    }
                }
            }
        }
    },
    changeSettings: function (uid, paramName, paramValue) {
        if (!sprint_editor.hasEntry(uid)) {
            return;
        }

        let entry = sprint_editor.getEntry(uid);

        if (typeof entry.changeSettings === 'function') {
            entry.changeSettings(paramName, paramValue);
        }
    },

    isEmptyData: function (uid) {
        let entry = sprint_editor.getEntry(uid);

        if (entry && typeof entry.isEmptyData === 'function') {
            return !!entry.isEmptyData();
        }

        return false;
    },

    collectData: function (uid) {
        let blockData = {};
        if (!sprint_editor.hasEntry(uid)) {
            return blockData;
        }

        let entry = sprint_editor.getEntry(uid);

        if (typeof entry.collectData !== 'function') {
            return blockData;
        }

        blockData = entry.collectData();

        if (typeof entry.getAreas !== 'function') {
            return blockData;
        }

        let areas = entry.getAreas();
        for (let prop in areas) {
            if (areas.hasOwnProperty(prop)) {
                let area = areas[prop];
                let adata = area.block.collectData();
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

        for (let prop in this._events[type]) {
            if (this._events[type].hasOwnProperty(prop)) {
                let event = this._events[type][prop];
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
            let val = JSON.parse(
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
        let val = {};
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
        let uniq = Math.random().toString(36).substring(2, 12);
        this._uidcounter++;

        prefix = (prefix) ? prefix : 'sp';

        return prefix + this._uidcounter + uniq;
    },

    renderBlock: function (blockData, blockSettings, uid, params) {
        let renderVars = this.getBlockParams(blockData.name);
        if (renderVars) {

            renderVars['uid'] = uid;
            renderVars['enableChange'] = params.enableChange;
            renderVars['compiled'] = sprint_editor.compileSettings(blockData, blockSettings);

            return this.renderTemplate('box', renderVars);
        }
        return '';
    },

    compileSettings: function (blockData, settings) {

        let compiled = [];

        if (!settings) {
            return compiled;
        }

        jQuery.each(settings, function (setName, setSet) {

            if (!setSet.value || !setSet.type || setSet.type !== 'select') {
                return true;
            }

            let value = [];
            let nothingSelected = true;

            jQuery.each(setSet.value, function (valVal, valTitle) {
                let isSelected = (
                    blockData.settings &&
                    blockData.settings[setName] &&
                    blockData.settings[setName] === valVal
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
                    if (valItem.value === setSet.default) {
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

        let selectedCss = cssstr.split(' ');

        let allclasses = {};
        if (params.jsonUserSettings.layout_classes) {
            if (params.jsonUserSettings.layout_classes[ltname]) {
                if (params.jsonUserSettings.layout_classes[ltname].length > 0) {
                    allclasses = params.jsonUserSettings.layout_classes[ltname]
                }
            }
        }

        let compiled = [];

        if (!allclasses) {
            return compiled;
        }

        jQuery.each(allclasses, function (groupIndex, groupClasses) {

            if (!jQuery.isArray(groupClasses)) {
                return true;
            }

            let value = [];
            let valSel = 0;

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
        if (params.jsonUserSettings.hasOwnProperty('layout_titles')) {
            if (params.jsonUserSettings.layout_titles[cssname]) {
                if (params.jsonUserSettings.layout_titles[cssname].length > 0) {
                    return params.jsonUserSettings.layout_titles[cssname];
                }
            }
        }

        return cssname;
    },

    getBlockSettings: function (name, params) {
        let blockSettings = {};
        if (
            params.hasOwnProperty('jsonUserSettings') &&
            params.jsonUserSettings.hasOwnProperty('block_settings') &&
            params.jsonUserSettings.block_settings.hasOwnProperty(name)

        ) {
            return params.jsonUserSettings.block_settings[name];

        }
        return blockSettings;
    },

    getLayoutSettings: function (name, params) {
        let layoutSettings = {};

        if (params.hasOwnProperty('jsonUserSettings') &&
            params.jsonUserSettings.hasOwnProperty('layout_settings') &&
            params.jsonUserSettings.layout_settings.hasOwnProperty(name)
        ) {
            return params.jsonUserSettings.layout_settings[name];

        }
        return layoutSettings;
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
        let settcnt = 0;
        let settval = {};

        $settings.find('.sp-x-settings-group').each(function () {
            let name = jQuery(this).data('name');
            let $val = jQuery(this).find('.sp-x-active').first();

            if ($val.length > 0) {
                settval[name] = $val.data('value');
                settcnt++;
            }
        });

        return settval;
    }
};
