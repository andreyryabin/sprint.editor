sprint_editor.registerBlock('accordion', function ($, $el, data, settings, currentEditorParams) {

    settings = settings || {};
    currentEditorParams = currentEditorParams || {};

    data = $.extend({
        items: [],
    }, data);

    var blocklist = [
        {id: 'htag', title: 'заголовок'},
        {id: 'text', title: 'текст'},
        {id: 'image', title: 'картинку'},
        {id: 'video', title: 'видео'},
        {id: 'lists', title: 'список'},
    ];

    if (settings.blocks && settings.blocks.value) {
        blocklist = [];
        $.each(settings.blocks.value, function (index, val) {
            blocklist.push({id: index, title: val})
        });
    }

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.items = [];

        var $container = $el.children('.sp-acc-container');

        $container.children('.sp-acc-tab').each(function () {
            var $tabblocks = $(this).children('.sp-acc-blocks');
            var $tabBtn1 = $(this).children('.sp-acc-header')

            var tab = {
                title: $tabBtn1.children('.sp-acc-tab-value').val(),
                collapsed: $(this).hasClass('sp-collapsed'),
                blocks: []
            };

            $tabblocks.children('.sp-x-box').each(function () {
                var blockData = sprint_editor.collectData(
                    $(this).data('uid')
                );

                blockData.settings = sprint_editor.collectSettings(
                    $(this).children('.sp-x-box-settings')
                );

                tab.blocks.push(blockData);
            });

            data.items.push(tab);

        });
        return data;
    };

    this.afterRender = function () {
        var $container = $el.children('.sp-acc-container');
        var $addTabBtn = $el.children('.sp-acc-add');

        $.each(data.items, function (index, item) {
            addTab(item);
        });

        $container.sortable({
            items: "> .sp-acc-tab",
            handle: ".sp-acc-tab-handle",
        });

        $addTabBtn.on('click', function (e) {
            addTab({
                title: '',
                blocks: []
            });
        });

        $el.on('click', '.sp-acc-collapse', function (e) {
            e.preventDefault();
            var $target = $(this).closest('.sp-acc-tab');

            toggleTab($target);
        });

        $el.on('click', '.sp-acc-del', function (e) {
            e.preventDefault();
            var $target = $(this).closest('.sp-acc-tab');

            $target.animate({opacity: 0}, 250, function () {
                $target.remove();
            })
        });

        $el.on('click', '.sp-acc-up', function (e) {
            e.preventDefault();
            var $block = $(this).closest('.sp-acc-tab');
            var $nblock = $block.prev('.sp-acc-tab');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
            }
        });

        $el.on('click', '.sp-acc-dn', function (e) {
            e.preventDefault();
            var $block = $(this).closest('.sp-acc-tab');
            var $nblock = $block.next('.sp-acc-tab');
            if ($nblock.length > 0) {
                $block.insertAfter($nblock);
            }
        });

        $el.on('click', '.sp-acc-box-del', function (e) {
            e.preventDefault();
            var $box = $(this).closest('.sp-x-box');

            var uid = $box.data('uid');
            sprint_editor.beforeDelete(uid);

            $box.animate({opacity: 0}, 250, function () {
                $box.remove();
            })
        });

        $el.on('click', '.sp-acc-box-up', function (e) {
            e.preventDefault();

            var $block = $(this).closest('.sp-x-box');

            var $nblock = $block.prev('.sp-x-box');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
                sprint_editor.afterSort($block.data('uid'));
            }
        });

        $el.on('click', '.sp-acc-box-dn', function (e) {
            e.preventDefault();

            var $block = $(this).closest('.sp-x-box');

            var $nblock = $block.next('.sp-x-box');
            if ($nblock.length > 0) {
                $block.insertAfter($nblock);
                sprint_editor.afterSort(
                    $block.data('uid')
                );
            }
        });

        function addTab(tabData) {
            var $tab = $(sprint_editor.renderTemplate('accordion-tab', {
                title: tabData.title,
                blocklist: blocklist
            }));

            if (tabData.collapsed) {
                hideTab($tab);
            }

            $container.append($tab);

            var $tabblocks = $tab.children('.sp-acc-blocks');
            var $buttons = $tab.children('.sp-acc-footer');
            var $header = $tab.children('.sp-acc-header');

            $.each(tabData.blocks, function (index, blockData) {
                addBlock(
                    blockData,
                    $tabblocks
                )
            });

            $header.on('dblclick', function () {
                toggleTab($tab)
            });

            $buttons.on('click', '.sp-acc-box-add', function () {
                addBlock(
                    {name: $(this).data('name')},
                    $tabblocks
                );
            });

            $tabblocks.sortable({
                items: "> .sp-x-box",
                handle: ".sp-acc-box-handle",
                connectWith: ".sp-acc-blocks",
            });
        }

        function isBlockEnabled(name) {
            var index = blocklist.findIndex(function (val) {
                return val.id === name;
            })

            return (index >= 0);
        }

        function addBlock(blockData, $tabblocks) {
            if (!isBlockEnabled(blockData.name)) {
                return;
            }

            var uid = sprint_editor.makeUid('sp-acc');
            var blockSettings = sprint_editor.getBlockSettings(blockData.name, currentEditorParams);

            var $box = $(sprint_editor.renderTemplate('accordion-box', {
                uid: uid,
                title: sprint_editor.getBlockTitle(blockData.name, currentEditorParams),
                box_settings: sprint_editor.renderTemplate(
                    'box-settings',
                    sprint_editor.compileSettings(blockData, blockSettings)
                ),
            }));

            $tabblocks.append($box);

            var $elBlock = $box.children('.sp-x-box-block');
            var elEntry = sprint_editor.initblock(
                $,
                $elBlock,
                blockData.name,
                blockData,
                blockSettings,
                currentEditorParams
            );

            sprint_editor.registerEntry(uid, elEntry);
        }

        function toggleTab($tab) {
            if ($tab.hasClass('sp-collapsed')) {
                showTab($tab);
            } else {
                hideTab($tab);
            }
            sprint_editor.fireEvent('popup:hide');
        }

        function showTab($tab) {
            var $tabblocks = $tab.children('.sp-acc-blocks');
            var $tabfooter = $tab.children('.sp-acc-footer');

            $tabblocks.show(250, function () {
                $tabfooter.show(250, function () {
                    $tab.removeClass('sp-collapsed')
                })
            })
        }

        function hideTab($tab) {
            var $tabblocks = $tab.children('.sp-acc-blocks');
            var $tabfooter = $tab.children('.sp-acc-footer');

            $tabblocks.hide(250, function () {
                $tabfooter.hide(250, function () {
                    $tab.addClass('sp-collapsed')
                })
            })


        }

    };
});
