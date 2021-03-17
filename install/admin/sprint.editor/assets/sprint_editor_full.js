function sprint_editor_full($, currentEditorParams, currentEditorValue) {
    var $editor = $('.sp-x-editor' + currentEditorParams.uniqid);
    var $inputresult = $('.sp-x-result' + currentEditorParams.uniqid);
    var $form = $editor.closest('form').first();

    $(document).keyup(function (e) {
        if (e.keyCode === 27) {
            popupToggle();
        }
    });

    $editor.on('keypress', 'input', function (e) {
        var keyCode = e.keyCode || e.which;
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
    if (currentEditorParams.hasOwnProperty('enablePacks')) {
        currentEditorParams.enablePacks = !!currentEditorParams.enablePacks;
    } else {
        currentEditorParams.enablePacks = true;
    }

    if (currentEditorParams.jsonUserSettings.hasOwnProperty('enable_change')) {
        currentEditorParams.enableChange = !!currentEditorParams.jsonUserSettings.enable_change;
    }

    currentEditorParams.deleteBlockAfterSortOut = false;
    if (currentEditorParams.jsonUserSettings.hasOwnProperty('delete_block_after_sort_out')) {
        currentEditorParams.deleteBlockAfterSortOut = !!currentEditorParams.jsonUserSettings.delete_block_after_sort_out;
    }

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
        var clipboardData = sprint_editor.getClipboard();

        $.each(clipboardData, function (blockUid, blockData) {
            if (blockData.deleteAfterPaste) {
                boxDelete(
                    $editor.find('.sp-x-box[data-uid=' + blockUid + ']')
                );
            }
        });
    });

    checkClipboardButtons();

    $form.on('submit', function (e) {
        var resultString = saveToString();

        $editor.find('input,textarea,select').removeAttr('name');
        $inputresult.val(resultString);
    });

    if (currentEditorParams.enableChange) {
        if (currentEditorParams.enablePacks) {
            packShow();
        }

        $editor.on('click', '.sp-x-col-copy', function (e) {
            e.preventDefault();
            var $grid = $(this).closest('.sp-x-lt');
            var $col = getActiveColumn($grid);

            $col.find('.sp-x-box').each(function () {
                sprint_editor.copyToClipboard($(this).data('uid'), false);
            });

            popupToggle();
        });

        $editor.on('click', '.sp-x-col-cut', function (e) {
            e.preventDefault();
            var $grid = $(this).closest('.sp-x-lt');
            var $col = getActiveColumn($grid);

            $col.find('.sp-x-box').each(function () {
                sprint_editor.copyToClipboard($(this).data('uid'), true);
            });

            popupToggle();
        });

        $editor.on('click', '.sp-x-col-paste', function (e) {
            e.preventDefault();

            var clipboardData = sprint_editor.getClipboard();
            var $grid = $(this).closest('.sp-x-lt');
            var $col = getActiveColumn($grid);

            $.each(clipboardData, function (blockUid, blockData) {
                blockAdd(blockData.block, $col);
            });

            sprint_editor.fireEvent('clipboard:paste');
            sprint_editor.clearClipboard();

            popupToggle();
        });

        $editor.on('click', '.sp-x-box-paste', function (e) {
            e.preventDefault();

            var clipboardData = sprint_editor.getClipboard();
            var $box = $(this).closest('.sp-x-box');

            $.each(clipboardData, function (blockUid, blockData) {
                blockAdd(blockData.block, $box);
            });

            sprint_editor.fireEvent('clipboard:paste');
            sprint_editor.clearClipboard();

            popupToggle();
        });

        $editor.on('click', '.sp-x-pp-blocks .sp-x-btn', function (e) {
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

        $editor.on('click', '.sp-x-lt-save', function (e) {
            e.preventDefault();
            var packname = prompt(BX.message('SPRINT_EDITOR_pack_change'));
            if (packname) {
                var $selectors = jQuery([]).pushStack($(this).closest('.sp-x-lt'));
                packSave('' + packname, $selectors);
                popupToggle($(this));
            }
        });

        $editor.on('click', '.sp-x-pp-lt-open', function (e) {
            e.preventDefault();
            popupToggle($(this));
        });
        /*$editor.on('mouseenter', '.sp-x-pp-lt-hover', function (e) {
            e.preventDefault();
            popupToggle($(this).children('.sp-x-pp-lt-open'),true);
        });*/


        $editor.on('click', '.sp-x-pp-box-open', function (e) {
            e.preventDefault();
            popupToggle($(this));
        });
        /*$editor.on('mouseenter', '.sp-x-pp-box-hover', function (e) {
            e.preventDefault();
            popupToggle($(this).children('.sp-x-pp-box-open'),true);
        });*/


        $editor.on('click', '.sp-x-pp-blocks-open', function (e) {
            e.preventDefault();
            popupToggle($(this));
        });

        $editor.on('click', '.sp-x-box-copy', function (e) {
            e.preventDefault();
            var $box = $(this).closest('.sp-x-box');
            sprint_editor.copyToClipboard($box.data('uid'), false);
            popupToggle();
        });

        $editor.on('click', '.sp-x-box-cut', function (e) {
            e.preventDefault();
            var $box = $(this).closest('.sp-x-box');
            sprint_editor.copyToClipboard($box.data('uid'), true);
            popupToggle();
        });

        $editor.on('click', '.sp-x-box-up', function (e) {
            e.preventDefault();

            var $block = $(this).closest('.sp-x-box');
            var $grid = $(this).closest('.sp-x-lt');

            var $nblock = $block.prev('.sp-x-box');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
                sprint_editor.afterSort($block.data('uid'));
            } else {
                var $ngrid = $grid.prev('.sp-x-lt');
                if ($ngrid.length > 0) {
                    var $ncol = getActiveColumn($ngrid);
                    $block.appendTo($ncol);
                    sprint_editor.afterSort($block.data('uid'));
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
                sprint_editor.afterSort($block.data('uid'));
            } else {
                var $ngrid = $grid.next('.sp-x-lt');
                if ($ngrid.length > 0) {
                    var $ncol = getActiveColumn($ngrid);
                    var $head = $ncol.find('.sp-x-col-settings');
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

        $editor.on('click', '.sp-x-col-left', function (e) {
            e.preventDefault();
            var $grid = $(this).closest('.sp-x-lt');
            var $tab = getActiveTab($grid);
            var $ntab = $tab.prev('.sp-x-col-tab');
            if ($ntab.length > 0) {
                $tab.insertBefore($ntab);
            }
        });

        $editor.on('click', '.sp-x-col-right', function (e) {
            e.preventDefault();
            var $grid = $(this).closest('.sp-x-lt');
            var $tab = getActiveTab($grid);
            var $ntab = $tab.next('.sp-x-col-tab');
            if ($ntab.length > 0) {
                $tab.insertAfter($ntab);
            }
        });

        $editor.on('click', '.sp-x-box-del', function (e) {
            e.preventDefault();
            var $box = $(this).closest('.sp-x-box');

            boxDelete($box);
        });

        $editor.on('click', '.sp-x-lt-toggle', function (e) {
            e.preventDefault();
            var $grid = $(this).closest('.sp-x-lt');
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

            var $grid = $(this).closest('.sp-x-lt');

            $grid.find('.sp-x-box').each(function () {
                var uid = $(this).data('uid');
                sprint_editor.beforeDelete(uid);
            });

            $grid.remove();

            popupToggle();
        });

        $editor.on('click', '.sp-x-col-edit', function (e) {
            var $grid = $(this).closest('.sp-x-lt');
            var $title = getActiveTab($grid).find('.sp-x-col-title');
            layoutEditColumnTitle($title);

        });
    }

    $editor.on('click', '.sp-x-box-collapse', function (e) {
        e.preventDefault();
        var $box = $(this).closest('.sp-x-box');
        $box.toggleClass('sp-x-box-collapsed');
        popupToggle();
    });

    $editor.on('click', '.sp-x-lt-collapse', function (e) {
        e.preventDefault();
        var $grid = $(this).closest('.sp-x-lt');
        var $col = getActiveColumn($grid);
        var first = true;
        var collapse = true;
        $col.find('.sp-x-box').each(function () {
            if (first) {
                collapse = $(this).hasClass('sp-x-box-collapsed')
                first = false;
            }
            if (collapse) {
                $(this).removeClass('sp-x-box-collapsed');
            } else {
                $(this).addClass('sp-x-box-collapsed');
            }
        });

        popupToggle();
    });

    $editor.on('click', '.sp-x-col-tab', function (e) {
        selectColumn($(this).data('uid'));
    });

    $editor.on('click', '.sp-x-box-settings span', function (e) {
        var $span = $(this);
        $span.siblings('span').removeClass('sp-x-active');
        $span.toggleClass('sp-x-active');
    });

    $editor.on('click', '.sp-x-lt-settings span', function (e) {
        var $span = $(this);
        $span.siblings('span').removeClass('sp-x-active');
        $span.toggleClass('sp-x-active');
    });

    $editor.on('click', '.sp-x-col-settings span', function (e) {
        var $span = $(this);
        $span.siblings('span').removeClass('sp-x-active');
        $span.toggleClass('sp-x-active');
    });

    function scrollTo($elem) {
        $(document).scrollTop($elem.offset().top - 80);
    }

    function popupToggle($handler, hoverMode) {
        hoverMode = !!hoverMode;

        function popupHide() {
            $editor.find('.sp-x-pp-box').hide();
            $editor.find('.sp-x-pp-lt').hide();
            $editor.find('.sp-x-pp-blocks').hide();
            $editor.find('.sp-x-pp-box-open').removeClass('sp-x-active');
            $editor.find('.sp-x-pp-lt-open').removeClass('sp-x-active');
            $editor.find('.sp-x-pp-blocks-open').removeClass('sp-x-active');
        }


        if (!$handler) {
            popupHide();
            return true;
        }

        var $popup;

        if ($handler.hasClass('sp-x-pp-lt-open')) {
            $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-lt');
        } else if ($handler.hasClass('sp-x-pp-box-open')) {
            $popup = $handler.closest('.sp-x-buttons').find('.sp-x-pp-box');
        } else if ($handler.hasClass('sp-x-pp-blocks-open')) {
            $popup = $editor.find('.sp-x-pp-blocks');
            if (!$popup || $popup.length <= 0) {
                $popup = $(sprint_editor.renderTemplate('pp-blocks' + currentEditorParams.uniqid, {}));
            }

            $handler.after($popup);
        }

        if (!$popup) {
            popupHide();
            return true;
        }

        if (hoverMode){
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
        var name = $handler.data('name');
        if (!name) {
            return false;
        }

        if (name.indexOf('layout_') === 0) {
            name = name.substr(7);
            layoutEmptyAdd(name);
            checkClipboardButtons();

        } else if (name.indexOf('pack_') === 0) {
            name = name.substr(5);
            packLoad(name);
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
        }
    }

    function addByNameBlock($handler) {
        var name = $handler.data('name');
        if (!name) {
            return false;
        }

        var $container = $handler.closest('.sp-x-box');
        if ($container.length <= 0) {
            $container = getActiveColumn(
                $handler.closest('.sp-x-lt')
            );
        }
        if ($container.length > 0) {
            var $box = blockAdd({name: name}, $container);

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
        var clipboardData = sprint_editor.getClipboard();

        var cntBlocks = 0;
        $editor.find('.sp-x-box')
            .removeClass('sp-x-box-copied')
            .removeClass('sp-x-box-cutted')
        ;


        $.each(clipboardData, function (blockUid, blockData) {
            var $box = $editor.find('.sp-x-box[data-uid=' + blockUid + ']');
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
        var ltname = 'type' + colCnt;

        var columns = [];
        var defaultclass = '';

        if (currentEditorParams.jsonUserSettings.layout_defaults) {
            if (currentEditorParams.jsonUserSettings.layout_defaults[ltname]) {
                defaultclass = currentEditorParams.jsonUserSettings.layout_defaults[ltname];
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
                title: columnTitle,
                enableChange: currentEditorParams.enableChange,
                enablePacks: currentEditorParams.enablePacks,
                compiled: sprint_editor.compileClasses(ltname, column.css, currentEditorParams)
            })
        });

        var layoutSettings = sprint_editor.getLayoutSettings(ltname, currentEditorParams);
        $editor.find('.sp-x-editor-lt').append(
            sprint_editor.renderTemplate('box-layout', {
                enableChange: currentEditorParams.enableChange,
                enablePacks: currentEditorParams.enablePacks,
                columns: columns,
                title: layoutTitle,
                compiled: sprint_editor.compileSettings(layout, layoutSettings)
            })
        );

        var $grid = $editor.find('.sp-x-lt').last();

        if (currentEditorParams.enableChange) {
            sortableBlocks($grid.find('.sp-x-col'));
        }

        selectColumn(firstUid);
        updateIndexes($grid);
    }

    function sortableBlocks($column) {
        var removeIntent = false;

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
                var uid = ui.item.data('uid');
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
        var uid = $box.data('uid');
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

        var uid = sprint_editor.makeUid();
        var blockSettings = sprint_editor.getBlockSettings(blockData.name, currentEditorParams);
        var $box = $(sprint_editor.renderBlock(blockData, blockSettings, uid, currentEditorParams));

        if (!$container || $container.length <= 0) {
            if (blockData.layout) {
                var pos = blockData.layout.split(',');
                var $grid = $editor.find('.sp-x-lt').eq(pos[0]);
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

        var $el = $box.find('.sp-x-box-block');
        var entry = sprint_editor.initblock(
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

    function packSave(packname, $selectors) {
        $.post('/bitrix/admin/sprint.editor/assets/backend/pack.php', {
            save: saveToString(packname, $selectors),
            userSettingsName: currentEditorParams.userSettingsName
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
            show: 1,
            userSettingsName: currentEditorParams.userSettingsName
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
            del: packname,
            userSettingsName: currentEditorParams.userSettingsName
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
            load: packname,
            userSettingsName: currentEditorParams.userSettingsName
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

    function getActiveColumnUid($grid) {
        var $column = getActiveColumn($grid);
        return $column.data('uid');
    }

    function getColumnTab(columnUid) {
        return $editor.find('.sp-x-col-tab[data-uid=' + columnUid + ']');
    }

    function getColumn(columnUid) {
        return $editor.find('.sp-x-col[data-uid=' + columnUid + ']');
    }

    function selectColumn(columnUid) {
        var $tab = getColumnTab(columnUid);
        var $column = getColumn(columnUid);

        if ($tab.length > 0) {
            $tab.siblings('.sp-x-col-tab').removeClass('sp-x-active');
            $tab.addClass('sp-x-active');
        }
        if ($column.length > 0) {
            $column.siblings('.sp-x-col').removeClass('sp-x-active');
            $column.addClass('sp-x-active');
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

    function saveToString(packname, $selectors) {
        packname = packname || '';
        $selectors = $selectors || $editor.find('.sp-x-lt');

        var blocks = [];
        var layouts = [];

        $selectors.each(function (gindex) {
            var columns = [];

            // var lttitle = $(this).find('.sp-x-lt-title').text();
            // var lttitle = BX.message('SPRINT_EDITOR_lt_default');

            var ltsettings = sprint_editor.collectSettings(
                $(this).find('.sp-x-lt-settings')
            );

            $(this).find('.sp-x-col-tab').each(function (cindex) {
                var $tab = $(this);

                var columnUid = $tab.data('uid');

                var $col = getColumn(columnUid);

                var $title = $tab.find('.sp-x-col-title');
                var coltitle = $title.text();

                var colclasses = [];
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

                    var uid = $(this).data('uid');

                    if (!sprint_editor.hasEntry(uid)) {
                        return true;
                    }

                    var blockData = sprint_editor.collectData(uid);

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

        var resultString = '';

        if (layouts.length > 0) {
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
