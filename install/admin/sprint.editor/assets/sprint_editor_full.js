function sprint_editor_full($, currentEditorParams, currentEditorValue) {
    let $editor = $('.sp-x-editor' + currentEditorParams.uniqid);

    let $inputresult = $('.sp-x-result' + currentEditorParams.uniqid);

    let $form = $editor.closest('form').first();

    if (!currentEditorValue.jsonValue) {
        currentEditorValue.jsonValue = {};
    }

    if (!currentEditorValue.jsonValue.blocks) {
        currentEditorValue.jsonValue.blocks = [];
    }

    if (!currentEditorValue.jsonValue.layouts) {
        currentEditorValue.jsonValue.layouts = [];
    }

    if (!currentEditorValue.jsonValue) {
        currentEditorValue.jsonValue = {};
    }

    if (!currentEditorParams.userSettingsName) {
        currentEditorParams.userSettingsName = '';
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

    if (currentEditorParams.jsonUserSettings.hasOwnProperty('enable_change')) {
        currentEditorParams.enableChange = !!currentEditorParams.jsonUserSettings.enable_change;
    }

    if (currentEditorParams.jsonUserSettings.hasOwnProperty('wide_mode')) {
        currentEditorParams.wideMode = !!currentEditorParams.jsonUserSettings.wide_mode;
    }

    currentEditorParams.deleteBlockAfterSortOut = false;
    if (currentEditorParams.jsonUserSettings.hasOwnProperty('delete_block_after_sort_out')) {
        currentEditorParams.deleteBlockAfterSortOut = !!currentEditorParams.jsonUserSettings.delete_block_after_sort_out;
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


    $(document).keyup(function (e) {
        if (e.keyCode === 27) {
            popupToggle();
        }
    });

    $editor.on('keypress', 'input', function (e) {
        let keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    $form.on('click', function (e) {
        if (!$(e.target).hasClass('sp-x-btn')) {
            popupToggle();
        }
    });

    $.each(currentEditorValue.jsonValue.layouts, function (index, layout) {
        layoutAdd(layout);
    });

    $.each(currentEditorValue.jsonValue.blocks, function (index, block) {
        blockAdd(block);
    });

    sprint_editor.listenEvent('window:focus', function () {
        checkClipboardButtons();
    });

    sprint_editor.listenEvent('clipboard:change', function () {
        checkClipboardButtons();
    });

    sprint_editor.listenEvent('clipboard:paste', function () {
        let clipboardData = sprint_editor.getClipboard();

        $.each(clipboardData, function (blockUid, blockData) {
            if (blockData.deleteAfterPaste) {
                boxDelete(
                    $editor.find('.sp-x-box[data-uid=' + blockUid + ']')
                );
            }
        });
    });

    checkClipboardButtons();

    $form.on('submit', function () {
        let resultString = saveToString();

        $editor.find('input,textarea,select').removeAttr('name');
        $inputresult.val(resultString);
    });

    $editor.on('click', '.sp-x-col-copy', function (e) {
        e.preventDefault();
        let $grid = $(this).closest('.sp-x-lt');
        let $col = getActiveColumn($grid);

        $col.find('.sp-x-box').each(function () {
            sprint_editor.copyToClipboard($(this).data('uid'), false);
        });

        popupToggle();
    });

    $editor.on('click', '.sp-x-col-cut', function (e) {
        e.preventDefault();
        let $grid = $(this).closest('.sp-x-lt');
        let $col = getActiveColumn($grid);

        $col.find('.sp-x-box').each(function () {
            sprint_editor.copyToClipboard($(this).data('uid'), true);
        });

        popupToggle();
    });

    $editor.on('click', '.sp-x-col-paste', function (e) {
        e.preventDefault();

        let clipboardData = sprint_editor.getClipboard();
        let $grid = $(this).closest('.sp-x-lt');
        let $col = getActiveColumn($grid);

        $.each(clipboardData, function (blockUid, blockData) {
            blockAdd(blockData.block, $col);
        });

        sprint_editor.fireEvent('clipboard:paste');
        sprint_editor.clearClipboard();

        popupToggle();
    });

    $editor.on('click', '.sp-x-box-paste', function (e) {
        e.preventDefault();

        let clipboardData = sprint_editor.getClipboard();
        let $box = $(this).closest('.sp-x-box');

        $.each(clipboardData, function (blockUid, blockData) {
            blockAdd(blockData.block, $box);
        });

        sprint_editor.fireEvent('clipboard:paste');
        sprint_editor.clearClipboard();

        popupToggle();
    });

    $editor.on('click', '.sp-x-toolbar .sp-x-btn', function (e) {
        e.preventDefault();
        addByNameBlock($(this));
    });

    $editor.on('click', '.sp-x-lastblock', function (e) {
        e.preventDefault();
        addByNameBlock($(this));
    });

    $editor.on('click', '.sp-x-footer .sp-x-btn', function (e) {
        e.stopPropagation();
        addByNameLayout($(this));
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
        sprint_editor.copyToClipboard($box.data('uid'), false);
        popupToggle();
    });

    $editor.on('click', '.sp-x-box-cut', function (e) {
        e.preventDefault();
        let $box = $(this).closest('.sp-x-box');
        sprint_editor.copyToClipboard($box.data('uid'), true);
        popupToggle();
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
                let $head = $ncol.find('.sp-x-col-settings');
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

    $editor.on('click', '.sp-x-lt-toggle', function (e) {
        e.preventDefault();
        let $grid = $(this).closest('.sp-x-lt');
        if ($grid.hasClass('sp-x-hidden')) {
            $grid.removeClass('sp-x-hidden');
            $(this).removeClass('sp-x-active');
        } else {
            $grid.addClass('sp-x-hidden');
            $(this).addClass('sp-x-active');
        }
    });

    $editor.on('click', '.sp-x-lt-del', function (e) {
        e.preventDefault();

        let $grid = $(this).closest('.sp-x-lt');

        $grid.find('.sp-x-box').each(function () {
            let uid = $(this).data('uid');
            sprint_editor.beforeDelete(uid);
        });

        $grid.remove();

        popupToggle();
    });

    $editor.on('click', '.sp-x-col-edit', function () {
        let $grid = $(this).closest('.sp-x-lt');
        let $title = getActiveTab($grid).find('.sp-x-col-title');
        layoutEditColumnTitle($title);

    });

    $editor.on('click', '.sp-x-box-collapse', function (e) {
        e.preventDefault();
        let $box = $(this).closest('.sp-x-box');
        $box.addClass('sp-x-box-collapsed');
        popupToggle();
    });

    $editor.on('click', '.sp-x-box-expand', function (e) {
        e.preventDefault();
        let $box = $(this).closest('.sp-x-box');
        $box.removeClass('sp-x-box-collapsed');
        popupToggle();
    });

    $editor.on('click', '.sp-x-lt-expand', function (e) {
        e.preventDefault();
        let $grid = $(this).closest('.sp-x-lt');
        let $col = getActiveColumn($grid);
        $col.find('.sp-x-box').each(function () {
            $(this).removeClass('sp-x-box-collapsed');
        });
        popupToggle();
    });

    $editor.on('click', '.sp-x-lt-collapse', function (e) {
        e.preventDefault();
        let $grid = $(this).closest('.sp-x-lt');
        let $col = getActiveColumn($grid);
        $col.find('.sp-x-box').each(function () {
            $(this).addClass('sp-x-box-collapsed');
        });
        popupToggle();
    });

    $editor.on('click', '.sp-x-col-tab', function () {
        selectColumn($(this).data('uid'));
    });
    $editor.on('click', '.sp-x-box-settings span', function () {
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

    $editor.on('click', '.sp-x-lt-settings span', function () {
        let $span = $(this);
        $span.siblings('span').removeClass('sp-x-active');
        $span.toggleClass('sp-x-active');
    });

    $editor.on('click', '.sp-x-col-settings span', function () {
        let $span = $(this);
        $span.siblings('span').removeClass('sp-x-active');
        $span.toggleClass('sp-x-active');
    });

    function popupToggle($handler, hoverMode) {
        hoverMode = !!hoverMode;

        function popupHide() {
            $editor.find('.sp-x-pp-box').hide();
            $editor.find('.sp-x-pp-lt').hide();
            $editor.find('.sp-x-toolbar').hide();
            $editor.find('.sp-x-pp-box-open').removeClass('sp-x-active');
            $editor.find('.sp-x-pp-lt-open').removeClass('sp-x-active');
            $editor.find('.sp-x-toolbar-open').removeClass('sp-x-active');
        }


        if (!$handler) {
            popupHide();
            return true;
        }

        let $popup;

        if ($handler.hasClass('sp-x-pp-lt-open')) {
            $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-lt');
        } else if ($handler.hasClass('sp-x-pp-box-open')) {
            $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-box');
        } else if ($handler.hasClass('sp-x-toolbar-open')) {
            $popup = $editor.find('.sp-x-toolbar');
            if (!$popup || $popup.length <= 0) {
                $popup = $(sprint_editor.renderTemplate('pp-blocks' + currentEditorParams.uniqid, {}));
            }

            $handler.after($popup);
        }

        if (!$popup) {
            popupHide();
            return true;
        }

        if (hoverMode) {
            if ($handler.hasClass('sp-x-active')) {
                return true;
            }
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

    function addByNameLayout($handler) {
        let name = $handler.data('name');
        if (!name) {
            return false;
        }

        if (name.indexOf('layout_') === 0) {
            name = name.substr(7);
            layoutEmptyAdd(name);
            checkClipboardButtons();

        } else {
            packLoad(name);
        }
    }

    function addByNameBlock($handler) {
        let name = $handler.data('name');
        if (!name) {
            return false;
        }

        let $container = $handler.closest('.sp-x-box');
        if ($container.length <= 0) {
            $container = getActiveColumn(
                $handler.closest('.sp-x-lt')
            );
        }
        if ($container.length > 0) {
            let $box = blockAdd({name: name}, $container);

            if ($box && !$handler.hasClass('sp-x-lastblock')) {
                $box.closest('.sp-x-lt').find('.sp-x-lastblock').html(
                    BX.message('SPRINT_EDITOR_add') + ' ' +
                    sprint_editor.getBlockTitle(name)
                ).data('name', name).show();
            }
        }


        popupToggle();
        checkClipboardButtons();
    }

    function checkClipboardButtons() {
        let clipboardData = sprint_editor.getClipboard();

        let cntBlocks = 0;
        $editor.find('.sp-x-box')
            .removeClass('sp-x-box-copied')
            .removeClass('sp-x-box-cutted')
        ;


        $.each(clipboardData, function (blockUid, blockData) {
            let $box = $editor.find('.sp-x-box[data-uid=' + blockUid + ']');
            if ($box.length > 0) {
                if (blockData.deleteAfterPaste) {
                    $box.addClass('sp-x-box-cutted');
                } else {
                    $box.addClass('sp-x-box-copied');
                }
            }
            cntBlocks++;
        });

        if (cntBlocks > 0) {
            $editor.find('.sp-x-col-paste').show();
            $editor.find('.sp-x-box-paste').show();
        } else {
            $editor.find('.sp-x-col-paste').hide();
            $editor.find('.sp-x-box-paste').hide();
        }
    }

    function layoutEmptyAdd(colCnt) {
        let ltname = 'type' + colCnt;

        let columns = [];
        let defaultclass = '';

        if (currentEditorParams.jsonUserSettings.hasOwnProperty('layout_defaults')) {
            if (currentEditorParams.jsonUserSettings.layout_defaults[ltname]) {
                defaultclass = currentEditorParams.jsonUserSettings.layout_defaults[ltname];
            }
        }

        for (let index = 1; index <= colCnt; index++) {
            columns.push({
                css: defaultclass
            })
        }

        layoutAdd({
            columns: columns
        });
    }

    function layoutAdd(layout) {
        let ltname = 'type' + layout.columns.length;

        let columns = [];

        let firstUid = '';

        let layoutTitle = (layout.title) ? layout.title : BX.message('SPRINT_EDITOR_lt_default');

        $.each(layout.columns, function (index, column) {
            let columnUid = sprint_editor.makeUid();

            if (!firstUid) {
                firstUid = columnUid;
            }

            let columnTitle = (column.title) ? column.title : BX.message('SPRINT_EDITOR_col_default');
            columns.push({
                uid: columnUid,
                title: columnTitle,
                enableChange: currentEditorParams.enableChange,
                compiled: sprint_editor.compileClasses(ltname, column.css, currentEditorParams)
            })
        });

        let layoutSettings = sprint_editor.getLayoutSettings(ltname, currentEditorParams);
        $editor.find('.sp-x-editor-lt').append(
            sprint_editor.renderTemplate('box-layout', {
                enableChange: currentEditorParams.enableChange,
                columns: columns,
                title: layoutTitle,
                compiled: sprint_editor.compileSettings(layout, layoutSettings)
            })
        );

        let $grid = $editor.find('.sp-x-lt').last();

        if (currentEditorParams.enableChange) {
            sortableBlocks($grid.find('.sp-x-col'));
        }

        selectColumn(firstUid);
        updateIndexes($grid);
    }

    function sortableBlocks($column) {
        let removeIntent = false;

        $column.sortable({
            items: ".sp-x-box",
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
                if (removeIntent && currentEditorParams.deleteBlockAfterSortOut) {
                    sprint_editor.beforeDelete(uid);
                    ui.item.remove();
                } else {
                    sprint_editor.afterSort(uid);
                }
            }
        })
    }

    function boxDelete($box) {
        let uid = $box.data('uid');
        sprint_editor.beforeDelete(uid);

        $box.hide(250, function () {
            $box.remove();
        });
    }

    function blockAdd(blockData, $container) {
        if (!blockData || !blockData.name) {
            return false;
        }

        if (!sprint_editor.hasBlockParams(blockData.name)) {
            return false;
        }

        let uid = sprint_editor.makeUid();
        let blockSettings = sprint_editor.getBlockSettings(blockData.name, currentEditorParams);
        let $box = $(sprint_editor.renderBlock(blockData, blockSettings, uid, currentEditorParams));

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

        if ($container.hasClass('sp-x-box')) {
            $box.insertAfter($container);
        } else {
            $container.append($box);
        }

        let $el = $box.find('.sp-x-box-block');
        let entry = sprint_editor.initblock(
            $,
            $el,
            blockData.name,
            blockData,
            blockSettings,
            currentEditorParams
        );

        sprint_editor.initblockAreas(
            $,
            $el,
            entry,
            currentEditorParams
        );

        sprint_editor.registerEntry(uid, entry);

        return $box;
        // scrollTo($el);
    }

    function packLoad(packname) {
        $.get('/bitrix/admin/sprint.editor/assets/backend/pack.php', {
            load: packname,
            userSettingsName: currentEditorParams.userSettingsName
        }, function (pack) {

            if (!pack || !pack.layouts || !pack.blocks) {
                return;
            }

            let layoutIndex = layoutCnt();

            $.each(pack.layouts, function (index, layout) {
                layoutAdd(layout)
            });

            $.each(pack.blocks, function (index, block) {
                let pos = block.layout;

                pos = pos.split(',');

                pos = [
                    parseInt(pos[0], 10) + layoutIndex,
                    parseInt(pos[1], 10)
                ];

                let newblock = $.extend({}, block, {
                    layout: pos.join(',')
                });

                blockAdd(newblock);
            });

            popupToggle();
            checkClipboardButtons();
        });


    }

    function layoutCnt() {
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

    function layoutEditColumnTitle($title) {
        let newtitle = prompt(BX.message('SPRINT_EDITOR_col_change'), $title.text());
        newtitle = $.trim(newtitle);

        if (newtitle) {
            $title.text(newtitle);
        }
    }

    function saveToString(packname, $selectors) {
        packname = packname || '';
        $selectors = $selectors || $editor.find('.sp-x-lt');

        let blocks = [];
        let layouts = [];

        $selectors.each(function (gindex) {
            let columns = [];

            // let lttitle = $(this).find('.sp-x-lt-title').text();
            // let lttitle = BX.message('SPRINT_EDITOR_lt_default');

            let ltsettings = sprint_editor.collectSettings(
                $(this).find('.sp-x-lt-settings')
            );

            $(this).find('.sp-x-col-tab').each(function (cindex) {
                let $tab = $(this);

                let columnUid = $tab.data('uid');

                let $col = getColumn(columnUid);

                let $title = $tab.find('.sp-x-col-title');
                let coltitle = $title.text();

                let colclasses = [];
                $col.find('.sp-x-col-settings .sp-x-active').each(function () {
                    colclasses.push(
                        $.trim(
                            $(this).data('value')
                        )
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

                    let uid = $(this).data('uid');

                    if (!sprint_editor.hasEntry(uid)) {
                        return true;
                    }

                    let blockData = sprint_editor.collectData(uid);

                    if (sprint_editor.isEmptyData(uid)){
                        return true;
                    }

                    blockData.settings = sprint_editor.collectSettings(
                        $(this).children('.sp-x-box-settings')
                    );

                    blockData.layout = gindex + ',' + cindex;
                    blocks.push(blockData);
                });

            });

            if (columns.length > 0) {
                layouts.push({
                    settings: ltsettings,
                    columns: columns
                });
            }

        });

        let resultString = '';

        if (layouts.length > 0 && blocks.length > 0) {
            resultString = sprint_editor.safeStringify({
                packname: packname,
                version: 2,
                userSettingsName: currentEditorParams.userSettingsName,
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
