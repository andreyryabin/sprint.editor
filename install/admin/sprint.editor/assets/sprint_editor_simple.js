function sprint_editor_simple($, currentEditorParams, currentEditorValue) {
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

    if (!currentEditorParams.jsonUserSettings) {
        currentEditorParams.jsonUserSettings = {};
    }

    if (currentEditorParams.hasOwnProperty('enableChange')) {
        currentEditorParams.enableChange = !!currentEditorParams.enableChange;
    } else {
        currentEditorParams.enableChange = true;
    }

    if (currentEditorParams.jsonUserSettings.hasOwnProperty('enable_change')) {
        currentEditorParams.enableChange = !!currentEditorParams.jsonUserSettings.enable_change;
    }

    currentEditorParams.deleteBlockAfterSortOut = false;
    if (currentEditorParams.jsonUserSettings.hasOwnProperty('delete_block_after_sort_out')) {
        currentEditorParams.deleteBlockAfterSortOut = !!currentEditorParams.jsonUserSettings.delete_block_after_sort_out;
    }

    layoutAdd();

    $.each(currentEditorValue.jsonValue.blocks, function (index, block) {
        block.layout = '0,0';
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

        $editor.on('click', '.sp-x-pp-blocks .sp-x-btn', function (e) {
            addByNameBlock($(this));
        });

        $editor.on('click', '.sp-x-lastblock', function (e) {
            addByNameBlock($(this));
        });

        $editor.on('click', '.sp-x-pp-lt-open', function (e) {
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

        $editor.on('click', '.sp-x-box-up', function (e) {
            e.preventDefault();
            var $block = $(this).closest('.sp-x-box');
            var $nblock = $block.prev('.sp-x-box');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
                sprint_editor.afterSort($block.data('uid'));
            }
        });

        $editor.on('click', '.sp-x-box-dn', function (e) {
            e.preventDefault();
            var $block = $(this).closest('.sp-x-box');
            var $nblock = $block.next('.sp-x-box');
            if ($nblock.length > 0) {
                $block.insertAfter($nblock);
                sprint_editor.afterSort($block.data('uid'));
            }
        });

        $editor.on('click', '.sp-x-box-del', function (e) {
            e.preventDefault();
            var $box = $(this).closest('.sp-x-box');
            boxDelete($box);
        });
    }


    $editor.on('click', '.sp-x-box-settings span', function (e) {
        var $span = $(this);
        $span.siblings('span').removeClass('sp-x-active');
        $span.toggleClass('sp-x-active');
    });


    function popupToggle($handler) {

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

        if ($handler.hasClass('sp-x-active')) {
            $handler.removeClass('sp-x-active');
            $popup.hide();
        } else {
            popupHide();
            $handler.addClass('sp-x-active');
            $popup.show();
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

    function layoutAdd() {
        if (currentEditorParams.enableChange) {
            sortableBlocks($editor.find('.sp-x-col').last());
        }
    }

    function sortableBlocks($column) {
        var removeIntent = false;

        $column.sortable({
            items: ".sp-x-box",
            connectWith: ".sp-x-col",
            handle: ".sp-x-box-handle",
            placeholder: "sp-x-box-placeholder",
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

    function saveToString(packname) {
        packname = packname || '';

        var blocks = [];

        $editor.find('.sp-x-lt').each(function (gindex) {
            $(this).find('.sp-x-col').each(function (cindex) {
                $(this).find('.sp-x-box').each(function () {

                    var uid = $(this).data('uid');

                    if (!sprint_editor.hasEntry(uid)) {
                        return true;
                    }

                    var blockData = sprint_editor.collectData(uid);

                    blockData.settings = sprint_editor.collectSettings(
                        $(this).find('.sp-x-box-settings')
                    );

                    blockData.layout = '0,0';
                    blocks.push(blockData);
                });

            });
        });

        var resultString = '';

        if (blocks.length > 0) {
            resultString = sprint_editor.safeStringify({
                packname: packname,
                version: 2,
                blocks: blocks,
                layouts: [
                    {
                        columns: [
                            {
                                css: ''
                            }
                        ]
                    }
                ]
            });
        }

        return resultString;
    }

    function getActiveColumn($grid) {
        return $grid.find('.sp-x-col.sp-x-active');
    }
}
