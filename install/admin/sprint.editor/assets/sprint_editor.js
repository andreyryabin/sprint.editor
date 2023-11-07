var sprint_editor = {
    _templates: {},
    _configs: {},
    _registered: {},
    _events: {},
    _entries: {},
    _uidcounter: 0,
    _submitcnt: 0,
    _clipboardUid: 'sprint-editor-cb02',

    registerBlock: function (name, method) {
        this._registered[name] = method;
    },

    registerConfigs: function (blocksConfigs) {
        this._configs = blocksConfigs
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

    setBlockConfig: function (name, params) {
        this._configs[name] = params;
    },

    hasBlockConfig: function (name) {
        return !!this._configs[name];
    },

    getBlockConfig: function (name, currentEditorParams) {
        currentEditorParams = currentEditorParams || {}

        if (this._configs[name]) {
            return Object.assign(
                {},
                this._configs[name],
                this.getBlockCustomConfig(name, currentEditorParams)
            );
        }

        return false;
    },

    registerDump(blockData) {
        if (!blockData.name) {
            return;
        }

        this.setBlockConfig(blockData.name, {
            'title': blockData.name,
            'hint': '',
            'sort': 100,
        });

        this.registerTemplate(
            blockData.name,
            '<div class="sp-x-dump-error">Block not found</div>'
        );

        this.registerBlock(blockData.name, function ($, $el, data) {
            this.getData = function () {
                return data;
            };
            this.collectData = function () {
                return data;
            };
            this.afterRender = function () {
            };
        });
    },

    getBlockTitle: function (name, currentEditorParams) {
        let blockConfig = this.getBlockConfig(name, currentEditorParams);
        if (blockConfig && blockConfig['title']) {
            return blockConfig['title'];
        }
        return '';
    },

    getBlockWebPath: function (blockName) {
        let values = this.getBlockConfig(blockName);
        if (values['islocal']) {
            return '/local/admin/sprint.editor/' + values['groupname'] + '/' + values['name'];
        } else {
            return '/bitrix/admin/sprint.editor/' + values['groupname'] + '/' + values['name'];
        }
    },

    renderTemplate: function (name, data) {
        if (!this._templates[name]) {
            let $tpl = jQuery('#sp-x-template-' + name);
            if ($tpl.length > 0) {
                this._templates[name] = $tpl.html();
            } else {
                this._templates[name] = '';
            }
        }

        if (window.doT) {
            let func = window.doT.template(
                this._templates[name]
            );
            return func(data);
        }

        return '';
    },

    renderString: function (str, data) {
        let func = window.doT.template(str);
        return func(data);
    },

    initblock: function ($, $el, name, blockData, blockSettings, currentEditorParams) {
        blockData = blockData || {}

        blockData['name'] = name;

        if (!sprint_editor.hasBlockMethod(name)) {
            sprint_editor.registerDump(blockData)
        }

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

        if (entry && typeof entry.getAreas == 'function') {
            let areas = entry.getAreas();
            for (let prop in areas) {
                if (areas.hasOwnProperty(prop)) {
                    let area = areas[prop];
                    let areaSettings = sprint_editor.getComplexSettings(
                        name,
                        area.blockName,
                        currentEditorParams
                    );
                    area.block = sprint_editor.initblock(
                        $,
                        $el.find(area.container),
                        area.blockName,
                        blockData[area.dataKey],
                        areaSettings,
                        currentEditorParams
                    );
                }
            }
        }

        return entry;
    },

    initblockAreas: function ($, $el, entry, currentEditorParams) {
        console.log('initblockAreas deprecated');
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

    collectDataFromEntry: function (entry) {
        let blockData = {};

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
                blockData[area.dataKey] = sprint_editor.collectDataFromEntry(area.block);
            }
        }

        return blockData;
    },

    collectData: function (uid) {
        let blockData = {};
        let entry = sprint_editor.getEntry(uid);
        if (entry) {
            blockData = sprint_editor.collectDataFromEntry(entry);
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

    renderBlock: function (blockData, blockSettings, uid, currentEditorParams) {
        let blockConfig = this.getBlockConfig(blockData.name, currentEditorParams);
        if (blockConfig) {
            blockConfig['uid'] = uid;
            blockConfig['blockName'] = blockData['name'];
            blockConfig['enableChange'] = currentEditorParams.enableChange;
            blockConfig['compiled'] = sprint_editor.compileSettings(blockData, blockSettings);

            return this.renderTemplate('box', blockConfig);
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

    getBlockCustomConfig: function (name, currentEditorParams) {
        if (
            currentEditorParams.hasOwnProperty('jsonUserSettings') &&
            currentEditorParams.jsonUserSettings.hasOwnProperty('block_configs') &&
            currentEditorParams.jsonUserSettings.block_configs.hasOwnProperty(name)

        ) {
            return currentEditorParams.jsonUserSettings.block_configs[name];

        }
        return {};
    },
    getBlockSettings: function (name, currentEditorParams) {
        if (
            currentEditorParams.hasOwnProperty('jsonUserSettings') &&
            currentEditorParams.jsonUserSettings.hasOwnProperty('block_settings') &&
            currentEditorParams.jsonUserSettings.block_settings.hasOwnProperty(name)

        ) {
            return currentEditorParams.jsonUserSettings.block_settings[name];

        }
        return {};
    },
    getComplexSettings: function (complexName, blockName, params) {
        if (
            params.hasOwnProperty('jsonUserSettings') &&
            params.jsonUserSettings.hasOwnProperty('complex_settings') &&
            params.jsonUserSettings.complex_settings.hasOwnProperty(complexName) &&
            params.jsonUserSettings.complex_settings[complexName].hasOwnProperty(blockName)
        ) {
            return params.jsonUserSettings.complex_settings[complexName][blockName];
        }
        return {};
    },
    getLayoutSettings: function (name, params) {
        if (params.hasOwnProperty('jsonUserSettings') &&
            params.jsonUserSettings.hasOwnProperty('layout_settings') &&
            params.jsonUserSettings.layout_settings.hasOwnProperty(name)
        ) {
            return params.jsonUserSettings.layout_settings[name];
        }
        return {};
    },

    getSnippets: function (params) {
        if (
            params.hasOwnProperty('jsonUserSettings') &&
            params.jsonUserSettings.hasOwnProperty('snippets')
        ) {
            return params.jsonUserSettings.snippets;
        }
        return [];
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
    },

};
