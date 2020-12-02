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
        $el.find('.sp-acc-tab').each(function () {
            var tab = {
                title: '',
                blocks: []
            };

            tab.title = $(this).find('.sp-acc-tab-value').val();

            $(this).find('.sp-acc-box').each(function () {
                var blockData = sprint_editor.collectData(
                    $(this).data('uid')
                );

                blockData.settings = sprint_editor.collectSettings(
                    $(this).find('.sp-x-box-settings')
                );

                tab.blocks.push(blockData);
            });

            data.items.push(tab);

        });
        return data;
    };

    this.afterRender = function () {

        $.each(data.items, function (index, item) {
            addTab(item);
        });

        $el.on('click', '.sp-acc-del', function (e) {
            e.preventDefault();
            var $target = $(this).closest('.sp-acc-tab');

            $target.hide(250, function () {
                $target.remove();
            });
        });

        $el.on('click', '.sp-acc-box-del', function (e) {
            e.preventDefault();
            var $target = $(this).closest('.sp-acc-box');

            var uid = $target.data('uid');
            sprint_editor.beforeDelete(uid);

            $target.hide(250, function () {
                $target.remove();
            });
        });

        $el.on('click', '.sp-acc-tab-buttons .sp-x-btn', function () {
            var $tabcontainer = $(this).closest('.sp-acc-tab').find('.sp-acc-tab-container');
            addblock(
                {
                    name: $(this).data('name')
                },
                $tabcontainer
            );
        });

        $el.sortable({
            items: ".sp-acc-tab",
            handle: ".sp-acc-tab-handle",
        });

        $el.find('.sp-acc-container').sortable({
            items: ".sp-acc-box",
            handle: ".sp-acc-box-handle",
            connectWith: ".sp-acc-tab-container",
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
            var block = $(this).closest('.sp-acc-tab');
            var nblock = block.next('.sp-acc-tab');
            if (nblock.length > 0) {
                block.insertAfter(nblock);
            }
        });

        $el.on('click', '.sp-acc-box-up', function (e) {
            e.preventDefault();

            var $block = $(this).closest('.sp-acc-box');
            var $grid = $(this).closest('.sp-acc-tab');

            var $nblock = $block.prev('.sp-acc-box');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
                sprint_editor.afterSort($block.data('uid'));
            } else {
                var $ngrid = $grid.prev('.sp-acc-tab');
                if ($ngrid.length > 0) {
                    $block.appendTo(
                        $ngrid.find('.sp-acc-tab-container')
                    );
                    sprint_editor.afterSort(
                        $block.data('uid')
                    );
                }
            }
        });

        $el.on('click', '.sp-acc-box-dn', function (e) {
            e.preventDefault();

            var $block = $(this).closest('.sp-acc-box');
            var $grid = $(this).closest('.sp-acc-tab');

            var $nblock = $block.next('.sp-acc-box');
            if ($nblock.length > 0) {
                $block.insertAfter($nblock);
                sprint_editor.afterSort(
                    $block.data('uid')
                );
            } else {
                var $ngrid = $grid.next('.sp-acc-tab');
                if ($ngrid.length > 0) {
                    $block.insertAfter(
                        $ngrid.find('.sp-acc-tab-container')
                    );
                    sprint_editor.afterSort(
                        $block.data('uid')
                    );
                }
            }
        });

        $el.on('click', '.sp-acc-add-tab', function (e) {
            addTab({
                title: '',
                blocks: []
            });
        });

        function addTab(tabData) {
            var $tab = $(sprint_editor.renderTemplate('accordion-tab', {
                title: tabData.title,
                blocklist: blocklist
            }));

            $el.find('.sp-acc-container').append($tab);

            var $tabcontainer = $tab.find('.sp-acc-tab-container');

            $.each(tabData.blocks, function (index, blockData) {
                addblock(blockData, $tabcontainer)
            });
        }

        function addblock(blockData, $tabcontainer) {
            var uid = sprint_editor.makeUid('sp-acc');
            var blockSettings = sprint_editor.getBlockSettings(blockData.name, currentEditorParams);

            var $box = $(sprint_editor.renderTemplate('accordion-box', {
                uid: uid,
                title: sprint_editor.getBlockTitle(blockData.name),
                compiled: sprint_editor.compileSettings(blockData, blockSettings)
            }));

            $tabcontainer.append($box);

            var $elBlock = $box.find('.sp-acc-box-block');
            var elEntry = sprint_editor.initblock(
                $,
                $elBlock,
                blockData.name,
                blockData,
                blockSettings,
                currentEditorParams
            );

            sprint_editor.initblockAreas(
                $,
                $elBlock,
                elEntry,
                currentEditorParams
            );
            sprint_editor.registerEntry(uid, elEntry);
        }
    };
});
