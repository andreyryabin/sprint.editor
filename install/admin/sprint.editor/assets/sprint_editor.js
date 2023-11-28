var sprint_editor = {
    _templates: {},
    _configs: {},
    _registered: {},
    _events: {},
    _entries: {},
    _uidcounter: 0,
    _submitcnt: 0,

    registerBlock: function (name, method) {
        this._registered[name] = method;
    },

    registerConfigs: function (blocksConfigs) {
        this._configs = blocksConfigs
    },

    registerTemplates: function (templates) {
        this._templates = templates;
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
        if (window.doT && this._templates.hasOwnProperty(name)) {
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
        blockData = blockData || {};
        blockSettings = blockSettings || {};
        currentEditorParams = currentEditorParams || {};

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

    /** @deprecated */
    initblockAreas: function () {
    },
    /** @deprecated */
    markImagesForDelete: function () {
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

    /** @deprecated */
    getClipboard: function () {
        return []
    },
    /** @deprecated */
    copyToClipboard: function () {
    },
    /** @deprecated */
    clearClipboard: function () {
    },

    /* clipboard api v2 */
    getClipboardV2: function (path) {
        let val = {};
        if (window.localStorage) {
            val = JSON.parse(localStorage.getItem(path));
        }

        return (val) ? val : [];
    },
    copyToClipboardV2: function (path, data) {
        if (window.localStorage) {
            let collection = JSON.parse(localStorage.getItem(path));
            collection = collection || [];

            collection.push(data);

            localStorage.setItem(path, JSON.stringify(collection));
            this.fireEvent('clipboard:change');
        }
    },
    clearClipboardV2: function (path) {
        if (window.localStorage) {
            localStorage.removeItem(path);
            this.fireEvent('clipboard:change');
        }
    },

    makeUid: function (prefix) {
        let uniq = Math.random().toString(36).substring(2, 12);
        this._uidcounter++;

        prefix = (prefix) ? prefix : 'sp';

        return prefix + this._uidcounter + uniq;
    },

    collectSettings: function ($settings) {
        let settval = {};

        $settings.find('.sp-x-settings-group').each(function () {
            let name = jQuery(this).data('name');
            let type = jQuery(this).data('type');

            if (type === 'select') {
                let $input = jQuery(this).find('.sp-x-active').first();
                if ($input.length > 0) {
                    settval[name] = $input.data('value');
                }
            }
            if (type === 'text') {
                let $input = jQuery(this).find('input').first();
                if ($input.length > 0) {
                    settval[name] = $input.val();
                }
            }
            if (type === 'dropdown') {
                let $input = jQuery(this).find('select').first();
                if ($input.length > 0) {
                    settval[name] = $input.val();
                }
            }
        });

        return settval;
    },

    compileSettings: function (blockData, settings) {
        let compiled = [];
        settings = settings || {};

        let blockSettings = {};
        if (blockData.hasOwnProperty('settings')) {
            blockSettings = Object.assign({}, blockData['settings']);
        }

        jQuery.each(settings, function (setName, setParams) {
            let setValue = '';
            if (blockSettings.hasOwnProperty(setName)) {
                setValue = blockData.settings[setName];
            }

            if ((setParams.type === 'select') || (setParams.type === 'dropdown')) {
                compiled.push(sprint_editor.compileSettingsSelect(setName, setValue, setParams));
            }

            if (setParams.type === 'text') {
                compiled.push(sprint_editor.compileSettingsText(setName, setValue, setParams));
            }
        });

        return compiled;
    },

    compileSettingsText: function (setName, setValue, setParams) {
        let defValue = '';
        if (setParams.hasOwnProperty('default')) {
            defValue = setParams.default
        }

        let strVal = String(setValue || defValue);

        return {
            name: setName,
            title: setParams.title || '',
            type: setParams.type,
            value: strVal,
            size: strVal.length + 1
        };
    },

    compileSettingsSelect: function (setName, setValue, setParams) {
        let options = [];
        let nothingSelected = true;

        jQuery.each(setParams.value, function (valVal, valTitle) {
            if (setValue === valVal) {
                nothingSelected = false;
            }

            options.push({
                title: valTitle,
                value: valVal,
                selected: setValue === valVal
            })
        });

        if (nothingSelected && setParams.hasOwnProperty('default')) {
            jQuery.each(options, function (index, valItem) {
                if (valItem.value === setParams.default) {
                    valItem.selected = true;
                }
            });
        }

        return {
            name: setName,
            title: setParams.title || '',
            type: setParams.type,
            options: options
        };
    },

    compileClasses: function (column, allclasses, alltitles) {
        let selectedCss = [];
        if (column.hasOwnProperty('css')) {
            selectedCss = column.css.split(' ');
        }

        let compiled = [];
        jQuery.each(allclasses, function (groupIndex, groupClasses) {

            if (!jQuery.isArray(groupClasses)) {
                return true;
            }

            let options = [];
            let valSel = 0;

            jQuery.each(groupClasses, function (cssIndex, cssName) {

                valSel = (jQuery.inArray(cssName, selectedCss) >= 0) ? 1 : 0;

                options.push({
                    title: alltitles[cssName] || cssName,
                    value: cssName,
                    selected: valSel
                })
            });

            compiled.push({
                options: options
            })
        });

        return compiled;
    },

    getBlockCustomConfig: function (name, currentEditorParams) {
        if (currentEditorParams &&
            currentEditorParams.hasOwnProperty('userSettings') &&
            currentEditorParams.userSettings.hasOwnProperty('block_configs') &&
            currentEditorParams.userSettings.block_configs.hasOwnProperty(name)
        ) {
            return currentEditorParams.userSettings.block_configs[name];

        }
        return {};
    },

    getBlockSettings: function (name, currentEditorParams) {
        if (currentEditorParams &&
            currentEditorParams.hasOwnProperty('userSettings') &&
            currentEditorParams.userSettings.hasOwnProperty('block_settings') &&
            currentEditorParams.userSettings.block_settings.hasOwnProperty(name)

        ) {
            return currentEditorParams.userSettings.block_settings[name];

        }
        return {};
    },

    getComplexSettings: function (complexName, blockName, currentEditorParams) {
        if (currentEditorParams &&
            currentEditorParams.hasOwnProperty('userSettings') &&
            currentEditorParams.userSettings.hasOwnProperty('complex_settings') &&
            currentEditorParams.userSettings.complex_settings.hasOwnProperty(complexName) &&
            currentEditorParams.userSettings.complex_settings[complexName].hasOwnProperty(blockName)
        ) {
            return currentEditorParams.userSettings.complex_settings[complexName][blockName];
        }
        return {};
    },

    getGridSettings: function (name, currentEditorParams) {
        if (currentEditorParams &&
            currentEditorParams.hasOwnProperty('userSettings') &&
            currentEditorParams.userSettings.hasOwnProperty('layout_settings') &&
            currentEditorParams.userSettings.layout_settings.hasOwnProperty(name)
        ) {
            return currentEditorParams.userSettings.layout_settings[name];
        }
        return {};
    },


    getColumnClasses: function (name, currentEditorParams) {
        if (currentEditorParams &&
            currentEditorParams.hasOwnProperty('userSettings') &&
            currentEditorParams.userSettings.hasOwnProperty('layout_classes') &&
            currentEditorParams.userSettings.layout_classes.hasOwnProperty(name) &&
            (currentEditorParams.userSettings.layout_classes[name].length > 0)
        ) {
            return currentEditorParams.userSettings.layout_classes[name];
        }

        return [];
    },

    getClassesTitles: function (currentEditorParams) {
        if (currentEditorParams &&
            currentEditorParams.hasOwnProperty('userSettings') &&
            currentEditorParams.userSettings.hasOwnProperty('classes_titles')
        ) {
            return currentEditorParams.userSettings.classes_titles || {};
        }
        return {};
    },

    getSnippets: function (currentEditorParams) {
        if (currentEditorParams &&
            currentEditorParams.hasOwnProperty('userSettings') &&
            currentEditorParams.userSettings.hasOwnProperty('snippets')
        ) {
            return currentEditorParams.userSettings.snippets;
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

    createInstance: function ($, currentEditorParams, currentEditorValue) {

        let $editor = $('.sp-x-editor' + currentEditorParams.uniqid);

        let $inputresult = $('.sp-x-result' + currentEditorParams.uniqid);

        let $form = $editor.closest('form').first();

        if (!currentEditorValue.blocks) {
            currentEditorValue.blocks = [];
        }

        if (!currentEditorValue.layouts) {
            currentEditorValue.layouts = [];
        }

        if (!currentEditorParams.userSettingsName) {
            currentEditorParams.userSettingsName = '';
        }

        currentEditorParams.clipboardBlock = 'sp-cb-b' + currentEditorParams.userSettingsName;
        currentEditorParams.clipboardGrid = 'sp-cb-g' + currentEditorParams.userSettingsName;

        if (!currentEditorParams.hasOwnProperty('blocksToolbar')) {
            currentEditorParams.blocksToolbar = [];
        }

        if (!currentEditorParams.hasOwnProperty('layoutsToolbar')) {
            currentEditorParams.layoutsToolbar = [];
        }

        currentEditorParams.enableLayoutsToolbar = false;
        if (currentEditorParams.enableChange) {
            if (currentEditorParams.layoutsToolbar.length > 0) {
                currentEditorParams.enableLayoutsToolbar = true;
            }
        }


        if (currentEditorParams.hasOwnProperty('saveEmpty')) {
            currentEditorParams.saveEmpty = !!currentEditorParams.saveEmpty;
        } else {
            currentEditorParams.saveEmpty = false;
        }

        if (currentEditorParams.hasOwnProperty('enableChange')) {
            currentEditorParams.enableChange = !!currentEditorParams.enableChange;
        } else {
            currentEditorParams.enableChange = true;
        }

        if (currentEditorParams.hasOwnProperty('wideMode')) {
            currentEditorParams.wideMode = !!currentEditorParams.wideMode;
        } else {
            currentEditorParams.wideMode = false;
        }

        if (currentEditorParams.wideMode) {
            (function () {
                let $bitrixCell = $editor.closest('td.adm-detail-content-cell-r');
                let $bitrixRow = $bitrixCell.closest('tr');
                if ($bitrixCell.length > 0 && $bitrixRow.length > 0) {
                    $bitrixRow.before('<tr class="heading"><td colspan="2">' + currentEditorParams.editorName + '</td></tr>');
                    $bitrixCell.removeAttr('class').removeAttr('width').attr('colspan', 2);
                    $bitrixCell.siblings('td.adm-detail-content-cell-l').remove();
                }
            })();
        }

        let $toolbarPopup = $(sprint_editor.renderTemplate('box-toolbar-popup', {
            'toolbar': currentEditorParams.blocksToolbar,
            'name': currentEditorParams.userSettingsName,
        }));

        let $clipboardFooter = $(sprint_editor.renderTemplate('box-clipboard-footer', {}));

        let $toolbarFooter = $(sprint_editor.renderTemplate('box-toolbar-footer', {
            'toolbar': currentEditorParams.layoutsToolbar,
            'name': currentEditorParams.userSettingsName,
        }));

        $editor.append('<div class="sp-x-editor-lt"></div>');
        if (currentEditorParams.enableChange) {
            $editor.append($clipboardFooter);
        }

        if (currentEditorParams.enableLayoutsToolbar) {
            $editor.append($toolbarFooter);
        }

        $(document).keyup(function (e) {
            if (e.keyCode === 27) {
                sprint_editor.fireEvent('popup:hide');
            }
        });

        $.each(currentEditorValue.layouts, function (index, layout) {
            gridAdd(layout);
        });

        $.each(currentEditorValue.blocks, function (index, block) {
            blockAdd(block);
        });

        sprint_editor.listenEvent('window:focus', function () {
            sprint_editor.fireEvent('clipboard:check');
        });

        sprint_editor.listenEvent('clipboard:change', function () {
            sprint_editor.fireEvent('clipboard:check');
            sprint_editor.fireEvent('popup:hide');
        });

        sprint_editor.listenEvent('popup:hide', function () {
            popupHide();
        });

        sprint_editor.listenEvent('clipboard:check', function () {
            clipboardCheck();
        });

        sprint_editor.fireEvent('clipboard:check');

        $editor.on('keypress', 'input', function (e) {
            let keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $form.on('click', function (e) {
            if (!$(e.target).hasClass('sp-x-btn') && !$(e.target).hasClass('sp-x-note')) {
                sprint_editor.fireEvent('popup:hide');
            }
        });

        $form.on('submit', function () {
            let resultString = saveToString();

            $editor.find('input,textarea,select').removeAttr('name');
            $inputresult.val(resultString);
        });

        $toolbarPopup.on('click', '.sp-x-btn', function (e) {
            e.preventDefault();
            addBlockByName($(this));
        });

        $toolbarFooter.on('click', '.sp-x-btn', function (e) {
            e.stopPropagation();
            addPackByName($(this));
        });

        $editor.on('click', '.sp-x-lastblock', function (e) {
            e.preventDefault();
            addBlockByName($(this));
        });


        $editor.on('click', '.sp-x-pp-lt-open', function (e) {
            e.preventDefault();
            popupToggle($(this));
        });

        $editor.on('click', '.sp-x-pp-box-open', function (e) {
            e.preventDefault();
            popupToggle($(this));
        });

        $editor.on('click', '.sp-x-toolbar-open', function (e) {
            e.preventDefault();
            popupToggle($(this));
        });

        $editor.on('click', '.sp-x-box-copy', function (e) {
            e.preventDefault();

            let $box = $(this).closest('.sp-x-box');
            if ($box.hasClass('sp-x-box-copied')) {
                return;
            }
            let boxData = saveBox($box);
            if (boxData) {
                $box.addClass('sp-x-box-copied');

                sprint_editor.copyToClipboardV2(
                    currentEditorParams.clipboardBlock,
                    boxData
                );
            }
        });

        $editor.on('click', '.sp-x-box-cut', function (e) {
            e.preventDefault();

            let $box = $(this).closest('.sp-x-box');

            let boxData = saveBox($box);
            if (boxData) {
                sprint_editor.copyToClipboardV2(
                    currentEditorParams.clipboardBlock,
                    boxData
                );

                boxDelete($box);
            }
        });

        $editor.on('click', '.sp-x-box-paste', function (e) {
            e.preventDefault();

            let clipboardData = sprint_editor.getClipboardV2(
                currentEditorParams.clipboardBlock
            );
            let $container = getBlockContainerByHandler($(this));

            $.each(clipboardData, function (index, blockData) {
                blockAdd(blockData, $container);
            });

            sprint_editor.clearClipboardV2(
                currentEditorParams.clipboardBlock
            );
        });

        $editor.on('click', '.sp-x-lt-copy', function (e) {
            e.preventDefault();

            let $grid = $(this).closest('.sp-x-lt');
            if ($grid.hasClass('sp-x-lt-copied')) {
                return;
            }

            $grid.addClass('sp-x-lt-copied');

            let gridData = saveGrid($grid, 0);

            sprint_editor.copyToClipboardV2(currentEditorParams.clipboardGrid, gridData);
        });

        $editor.on('click', '.sp-x-lt-cut', function (e) {
            e.preventDefault();
            let $grid = $(this).closest('.sp-x-lt');

            let gridData = saveGrid($grid, 0);

            sprint_editor.copyToClipboardV2(currentEditorParams.clipboardGrid, gridData);

            gridDelete($grid);
        });

        $editor.on('click', '.sp-x-lt-paste', function (e) {
            e.preventDefault();

            let clipboardData = sprint_editor.getClipboardV2(currentEditorParams.clipboardGrid);
            // let $grid = $(this).closest('.sp-x-lt');

            $.each(clipboardData, function (index, gridData) {
                packLoad(gridData)
            });

            sprint_editor.clearClipboardV2(currentEditorParams.clipboardGrid);
        });

        $editor.on('click', '.sp-x-box-up', function (e) {
            e.preventDefault();

            let $block = $(this).closest('.sp-x-box');
            let $grid = $(this).closest('.sp-x-lt');

            let $nblock = $block.prev('.sp-x-box');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
                sprint_editor.afterSort($block.data('uid'));
            } else {
                let $ngrid = $grid.prev('.sp-x-lt');
                if ($ngrid.length > 0) {
                    let $ncol = getActiveColumn($ngrid);
                    $block.appendTo($ncol);
                    sprint_editor.afterSort($block.data('uid'));
                }
            }
        });

        $editor.on('click', '.sp-x-box-dn', function (e) {
            e.preventDefault();

            let $block = $(this).closest('.sp-x-box');
            let $grid = $(this).closest('.sp-x-lt');

            let $nblock = $block.next('.sp-x-box');
            if ($nblock.length > 0) {
                $block.insertAfter($nblock);
                sprint_editor.afterSort($block.data('uid'));
            } else {
                let $ngrid = $grid.next('.sp-x-lt');
                if ($ngrid.length > 0) {
                    let $ncol = getActiveColumn($ngrid);
                    let $head = $ncol.find('.sp-x-col-classes');
                    if ($head.length > 0) {
                        $block.insertAfter($head);
                        sprint_editor.afterSort($block.data('uid'));
                    } else {
                        $block.prependTo($ncol);
                        sprint_editor.afterSort($block.data('uid'));
                    }
                }
            }
        });

        $editor.on('click', '.sp-x-lt-up', function (e) {
            e.preventDefault();
            let $block = $(this).closest('.sp-x-lt');
            let $nblock = $block.prev('.sp-x-lt');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
            }
        });

        $editor.on('click', '.sp-x-lt-dn', function (e) {
            e.preventDefault();
            let $block = $(this).closest('.sp-x-lt');
            let $nblock = $block.next('.sp-x-lt');
            if ($nblock.length > 0) {
                $block.insertAfter($nblock);
            }
        });

        $editor.on('click', '.sp-x-col-left', function (e) {
            e.preventDefault();
            let $grid = $(this).closest('.sp-x-lt');
            let $tab = getActiveTab($grid);
            let $ntab = $tab.prev('.sp-x-col-tab');
            if ($ntab.length > 0) {
                $tab.insertBefore($ntab);
            }
        });

        $editor.on('click', '.sp-x-col-right', function (e) {
            e.preventDefault();
            let $grid = $(this).closest('.sp-x-lt');
            let $tab = getActiveTab($grid);
            let $ntab = $tab.next('.sp-x-col-tab');
            if ($ntab.length > 0) {
                $tab.insertAfter($ntab);
            }
        });

        $editor.on('click', '.sp-x-box-del', function (e) {
            e.preventDefault();
            let $box = $(this).closest('.sp-x-box');

            boxDelete($box);
        });

        $editor.on('click', '.sp-x-lt-del', function (e) {
            e.preventDefault();

            let $grid = $(this).closest('.sp-x-lt');
            if (getGridCount() > 1) {
                gridDelete($grid);
            } else {
                gridClear($grid);
            }

            sprint_editor.fireEvent('popup:hide');
        });

        $editor.on('click', '.sp-x-col-edit', function () {
            let $grid = $(this).closest('.sp-x-lt');
            let $title = getActiveTab($grid).find('.sp-x-col-title');
            gridEditColumnTitle($title);

        });

        $editor.on('click', '.sp-x-box-hide', function (e) {
            e.preventDefault();
            let $box = $(this).closest('.sp-x-box');
            if ($box.hasClass('sp-x-box-hidden')) {
                $box.removeClass('sp-x-box-hidden');
                $box.removeClass('sp-x-box-collapsed');
            } else {
                $box.addClass('sp-x-box-hidden');
                $box.addClass('sp-x-box-collapsed');
            }
            sprint_editor.fireEvent('popup:hide');
        });

        $editor.on('click', '.sp-x-box-collapse', function (e) {
            e.preventDefault();
            let $box = $(this).closest('.sp-x-box');
            if ($box.hasClass('sp-x-box-collapsed')) {
                $box.removeClass('sp-x-box-collapsed');
            } else {
                $box.addClass('sp-x-box-collapsed');
            }
            sprint_editor.fireEvent('popup:hide');
        });

        $editor.on('click', '.sp-x-lt-expand', function (e) {
            e.preventDefault();
            let $grid = $(this).closest('.sp-x-lt');
            let $col = getActiveColumn($grid);
            $col.children('.sp-x-box').each(function () {
                $(this).removeClass('sp-x-box-collapsed');
            });
            sprint_editor.fireEvent('popup:hide');
        });

        $editor.on('click', '.sp-x-lt-collapse', function (e) {
            e.preventDefault();
            let $grid = $(this).closest('.sp-x-lt');
            let $col = getActiveColumn($grid);
            $col.children('.sp-x-box').each(function () {
                $(this).addClass('sp-x-box-collapsed');
            });
            sprint_editor.fireEvent('popup:hide');
        });

        $editor.on('click', '.sp-x-col-tab', function () {
            selectColumn($(this).data('uid'));
        });

        $editor.on('click', '.sp-x-box-settings .sp-x-btn', function () {
            let $span = $(this);
            $span.siblings('span').removeClass('sp-x-active');
            $span.toggleClass('sp-x-active');

            let $block = $span.closest('.sp-x-box');
            let $group = $span.parent();

            sprint_editor.changeSettings(
                $block.data('uid'),
                $group.data('name'),
                $span.hasClass('sp-x-active') ? $span.data('value') : ''
            );
        });

        $editor.on('click', '.sp-x-lt-settings .sp-x-btn', function () {
            let $span = $(this);
            $span.siblings('span').removeClass('sp-x-active');
            $span.toggleClass('sp-x-active');
        });

        $editor.on('click', '.sp-x-col-classes .sp-x-btn', function () {
            let $span = $(this);
            $span.siblings('span').removeClass('sp-x-active');
            $span.toggleClass('sp-x-active');
        });

        $editor.on('input', '.sp-x-input-txt input', function () {
            $(this).attr('size', $(this).val().length + 1);
        });

        function popupHide() {
            $editor.find('.sp-x-pp-box').hide();
            $editor.find('.sp-x-pp-lt').hide();
            $toolbarPopup.hide();
            $editor.find('.sp-x-pp-box-open').removeClass('sp-x-active');
            $editor.find('.sp-x-pp-lt-open').removeClass('sp-x-active');
            $editor.find('.sp-x-toolbar-open').removeClass('sp-x-active');
        }

        function popupToggle($handler) {

            if (!$handler) {
                popupHide();
                return true;
            }

            if ($handler.hasClass('sp-x-toolbar-open')) {
                if ($handler.hasClass('sp-x-active')) {
                    $handler.removeClass('sp-x-active');
                    $toolbarPopup.hide();
                } else {
                    popupHide();
                    $handler.addClass('sp-x-active');
                    $handler.after($toolbarPopup);
                    $toolbarPopup.show();
                }
                return true;
            }


            let $popup;
            if ($handler.hasClass('sp-x-pp-lt-open')) {
                $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-lt');
            } else if ($handler.hasClass('sp-x-pp-box-open')) {
                $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-box');
            }

            if (!$popup) {
                popupHide();
                return true;
            }

            if ($handler.hasClass('sp-x-active')) {
                $handler.removeClass('sp-x-active');
                $popup.hide();
            } else {
                popupHide();
                $handler.addClass('sp-x-active');
                $popup.show();
            }
        }

        function clipboardCheck() {
            let blocks = sprint_editor.getClipboardV2(
                currentEditorParams.clipboardBlock
            );
            if (blocks && blocks.length > 0) {
                $toolbarPopup.find('.sp-x-box-clipboard').show();
            } else {
                $editor.find('.sp-x-box-copied').removeClass('sp-x-box-copied');
                $toolbarPopup.find('.sp-x-box-clipboard').hide();
            }

            let grids = sprint_editor.getClipboardV2(currentEditorParams.clipboardGrid);
            if (grids && grids.length > 0) {
                $clipboardFooter.show();
            } else {
                $editor.find('.sp-x-lt-copied').removeClass('sp-x-lt-copied');
                $clipboardFooter.hide();
            }
        }

        function addPackByName($handler) {
            let name = $handler.data('name');
            if (!name) {
                return false;
            }

            if (name.indexOf('layout_') === 0) {
                name = name.substring(7);
                gridEmptyAdd(name);
                sprint_editor.fireEvent('clipboard:check');

            } else {
                packLoadByName(name);
            }
        }

        function addBlockByName($handler) {
            let name = $handler.data('name');
            if (!name) {
                return false;
            }

            let $container = getBlockContainerByHandler($handler);
            if ($container) {
                let $box = blockAdd({name: name}, $container);

                if ($box && !$handler.hasClass('sp-x-lastblock')) {
                    $box.closest('.sp-x-lt').find('.sp-x-lastblock').html(
                        BX.message('SPRINT_EDITOR_add') + ' ' + sprint_editor.getBlockTitle(name, currentEditorParams)
                    ).data('name', name).show();
                }
            }

            sprint_editor.fireEvent('popup:hide');
            sprint_editor.fireEvent('clipboard:check');
        }

        function getBlockContainerByHandler($handler) {
            let $container = $handler.closest('.sp-x-box');
            if ($container.length > 0) {
                return $container;
            }
            $container = getActiveColumn($handler.closest('.sp-x-lt'));
            if ($container.length > 0) {
                return $container;
            }

            return false;
        }


        function gridEmptyAdd(colCnt) {
            let ltname = 'type' + colCnt;

            let columns = [];
            let defaultclass = '';

            if (currentEditorParams.userSettings.hasOwnProperty('layout_defaults')) {
                if (currentEditorParams.userSettings.layout_defaults[ltname]) {
                    defaultclass = currentEditorParams.userSettings.layout_defaults[ltname];
                }
            }

            for (let index = 1; index <= colCnt; index++) {
                columns.push({
                    css: defaultclass
                })
            }

            gridAdd({
                columns: columns
            });
        }

        function gridAdd(gridData) {
            let ltname = 'type' + gridData.columns.length;

            let columns = [];

            let firstUid = '';

            let layoutTitle = (gridData.title) ? gridData.title : BX.message('SPRINT_EDITOR_lt_default');

            let classesTitles = sprint_editor.getClassesTitles(currentEditorParams);

            $.each(gridData.columns, function (index, column) {
                let columnUid = sprint_editor.makeUid();

                if (!firstUid) {
                    firstUid = columnUid;
                }

                let columnTitle = (column.title) ? column.title : BX.message('SPRINT_EDITOR_col_default');
                columns.push({
                    uid: columnUid,
                    title: columnTitle,
                    enableChange: currentEditorParams.enableChange,
                    column_classes: sprint_editor.renderTemplate(
                        'box-classes',
                        sprint_editor.compileClasses(
                            column,
                            sprint_editor.getColumnClasses(ltname, currentEditorParams),
                            classesTitles
                        )
                    )
                })
            });

            let $grid = $(sprint_editor.renderTemplate(
                'box-layout', {
                    enableChange: currentEditorParams.enableChange,
                    columns: columns, title: layoutTitle,
                    layout_settings: sprint_editor.renderTemplate(
                        'box-settings',
                        sprint_editor.compileSettings(
                            gridData,
                            sprint_editor.getGridSettings(ltname, currentEditorParams)
                        )
                    )
                }));

            $editor.children('.sp-x-editor-lt').append($grid);

            if (currentEditorParams.enableChange) {
                sortableBlocks($grid.find('.sp-x-col'));
            }

            selectColumn(firstUid);
            updateIndexes($grid);
        }

        function sortableBlocks($column) {
            let removeIntent = false;

            $column.sortable({
                items: "> .sp-x-box",
                connectWith: ".sp-x-col",
                handle: ".sp-x-box-handle",
                over: function () {
                    removeIntent = false;
                },
                out: function () {
                    removeIntent = true;
                },
                beforeStop: function (event, ui) {
                    let uid = ui.item.data('uid');
                    sprint_editor.afterSort(uid);
                }
            })
        }

        function boxDelete($box) {
            let uid = $box.data('uid');
            sprint_editor.beforeDelete(uid);

            $box.animate({opacity: 0}, 250, function () {
                $box.remove();
            })
        }

        function gridDelete($grid) {
            $grid.find('.sp-x-box').each(function () {
                let uid = $(this).data('uid');
                sprint_editor.beforeDelete(uid);
            });

            $grid.animate({opacity: 0}, 250, function () {
                $grid.remove();
            })
        }

        function gridClear($grid) {
            $grid.find('.sp-x-box').each(function () {
                boxDelete($(this));
            });
        }

        function blockAdd(blockData, $container) {
            if (!blockData || !blockData.name) {
                return false;
            }

            if (!sprint_editor.hasBlockConfig(blockData.name)) {
                sprint_editor.registerDump(blockData);
            }

            let uid = sprint_editor.makeUid();
            let blockSettings = sprint_editor.getBlockSettings(blockData.name, currentEditorParams);
            let blockConfig = sprint_editor.getBlockConfig(blockData.name, currentEditorParams);

            let $box = $(sprint_editor.renderTemplate('box', Object.assign(blockConfig, {
                uid: uid,
                blockName: blockData['name'],
                enableChange: currentEditorParams.enableChange,
                box_settings: sprint_editor.renderTemplate(
                    'box-settings',
                    sprint_editor.compileSettings(blockData, blockSettings)
                ),
            })));

            if (!$container || $container.length <= 0) {
                if (blockData.layout) {
                    let pos = blockData.layout.split(',');
                    let $grid = $editor.find('.sp-x-lt').eq(pos[0]);
                    $container = $grid.find('.sp-x-col').eq(pos[1]);
                }
            }

            if (!$container || $container.length <= 0) {
                return false;
            }

            if (blockData.meta && blockData.meta.collapsed) {
                $box.addClass('sp-x-box-collapsed')
            }

            if (blockData.meta && blockData.meta.hidden) {
                $box.addClass('sp-x-box-hidden')
            }

            if ($container.hasClass('sp-x-box')) {
                $box.insertAfter($container);
            } else {
                $container.append($box);
            }

            let $el = $box.children('.sp-x-box-block');
            let entry = sprint_editor.initblock(
                $,
                $el,
                blockData.name,
                blockData,
                blockSettings,
                currentEditorParams
            );

            sprint_editor.registerEntry(uid, entry);

            return $box;
        }

        function packLoadByName(packname) {
            $.get('/bitrix/admin/sprint.editor/assets/backend/pack.php', {
                load: packname, userSettingsName: currentEditorParams.userSettingsName
            }, function (packData) {
                packLoad(packData);
            });
        }

        function packLoad(packData) {

            if (!packData || !packData.layouts || !packData.blocks) {
                return;
            }

            let gindex = getGridCount();

            $.each(packData.layouts, function (index, layout) {
                gridAdd(layout)
            });

            $.each(packData.blocks, function (index, block) {
                let pos = block.layout;

                pos = pos.split(',');

                pos = [parseInt(pos[0], 10) + gindex, parseInt(pos[1], 10)];

                let newblock = $.extend({}, block, {
                    layout: pos.join(',')
                });

                blockAdd(newblock);
            });

            sprint_editor.fireEvent('popup:hide');
            sprint_editor.fireEvent('clipboard:check');
        }

        function getGridCount() {
            return $editor.find('.sp-x-lt').length;
        }

        function getActiveColumn($grid) {
            return $grid.find('.sp-x-col.sp-x-active');
        }

        function getActiveTab($grid) {
            return $grid.find('.sp-x-col-tab.sp-x-active');
        }

        function getColumnTab(columnUid) {
            return $editor.find('.sp-x-col-tab[data-uid=' + columnUid + ']');
        }

        function getColumn(columnUid) {
            return $editor.find('.sp-x-col[data-uid=' + columnUid + ']');
        }

        function selectColumn(columnUid) {
            let $tab = getColumnTab(columnUid);
            let $column = getColumn(columnUid);

            if ($tab.length > 0) {
                $tab.siblings('.sp-x-col-tab').removeClass('sp-x-active');
                $tab.addClass('sp-x-active');
            }
            if ($column.length > 0) {
                $column.siblings('.sp-x-col').removeClass('sp-x-active');
                $column.addClass('sp-x-active');
            }
        }

        function gridEditColumnTitle($title) {
            let newtitle = prompt(BX.message('SPRINT_EDITOR_col_change'), $title.text());
            newtitle = $.trim(newtitle);

            if (newtitle) {
                $title.text(newtitle);
            }
        }

        function saveBox($box) {

            let uid = $box.data('uid');

            if (!sprint_editor.hasEntry(uid)) {
                return false;
            }

            if (sprint_editor.isEmptyData(uid)) {
                return false;
            }

            let blockData = sprint_editor.collectData(uid);

            blockData.settings = sprint_editor.collectSettings(
                $box.children('.sp-x-box-settings')
            );

            let meta = {};
            if ($box.hasClass('sp-x-box-collapsed')) {
                meta['collapsed'] = true;
            }
            if ($box.hasClass('sp-x-box-hidden')) {
                meta['hidden'] = true;
            }

            blockData.layout = '0,0';
            blockData.meta = meta;

            return blockData;
        }

        function saveGrid($grid, gindex) {
            let blocks = [];
            let columns = [];

            let $ltbuttons = $grid.children('.sp-x-buttons-lt1');

            let ltsettings = sprint_editor.collectSettings(
                $ltbuttons.children('.sp-x-lt-settings')
            );

            $ltbuttons.find('.sp-x-col-tab').each(function (cindex) {
                let $tab = $(this);

                let columnUid = $tab.data('uid');

                let $col = getColumn(columnUid);

                let $title = $tab.find('.sp-x-col-title');
                let coltitle = $title.text();

                let colclasses = [];
                $col.children('.sp-x-col-classes').find('.sp-x-active').each(function () {
                    colclasses.push($.trim($(this).data('value')));
                });

                if (coltitle !== BX.message('SPRINT_EDITOR_col_default')) {
                    columns.push({
                        title: coltitle, css: colclasses.join(' ')
                    });
                } else {
                    columns.push({
                        css: colclasses.join(' ')
                    });
                }

                $col.children('.sp-x-box').each(function () {
                    let blockData = saveBox($(this), gindex, cindex);
                    if (blockData && blockData.name) {
                        blockData.layout = gindex + ',' + cindex;
                        blocks.push(blockData);
                    }
                });
            });

            return {
                blocks: blocks,
                layouts: [
                    {
                        settings: ltsettings,
                        columns: columns
                    }
                ],
            }
        }

        function saveToString() {
            let blocks = [];
            let layouts = [];

            $editor.find('.sp-x-lt').each(function (gindex) {
                let gridData = saveGrid($(this), gindex);

                $.each(gridData.blocks, function (index, block) {
                    blocks.push(block);
                });

                $.each(gridData.layouts, function (index, layout) {
                    layouts.push(layout);
                });
            });

            let resultString = '';
            if (currentEditorParams.saveEmpty || (layouts.length > 0 && blocks.length > 0)) {
                resultString = sprint_editor.safeStringify({
                    version: 2,
                    blocks: blocks,
                    layouts: layouts
                });
            }

            return resultString;
        }

        function updateIndexes($grid) {

            $grid.find('.sp-x-col-index').each(function (cindex) {
                $(this).text(cindex + 1);
            });

        }
    }
};
