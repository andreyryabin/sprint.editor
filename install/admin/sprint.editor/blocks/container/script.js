sprint_editor.registerBlock('container', function ($, $el, data, settings, currentEditorParams) {

    settings = settings || {};
    currentEditorParams = currentEditorParams || {};

    data = $.extend({
        blocks: [],
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
        data['blocklist'] = blocklist;
        return data;
    };

    this.collectData = function () {
        var $container = $el.children('.sp-items');

        data.blocks = [];
        $container.children('.sp-acc-box').each(function () {
            var blockData = sprint_editor.collectData(
                $(this).data('uid')
            );

            blockData.settings = sprint_editor.collectSettings(
                $(this).children('.sp-x-box-settings')
            );

            data.blocks.push(blockData);
        });
        delete data['blocklist'];
        return data;
    };

    this.afterRender = function () {
        var $container = $el.children('.sp-items');
        var $buttons = $el.children('.sp-buttons');

        $.each(data.blocks, function (index, blockData) {
            addblock(blockData, $container);
        });

        $container.sortable({
            items: "> div",
            handle: ".sp-acc-box-handle",
        });

        $buttons.on('click', '.sp-x-btn', function () {
            addblock(
                {
                    name: $(this).data('name')
                },
                $container
            );
        });

        function addblock(blockData, $container) {
            var uid = sprint_editor.makeUid('sp-acc');
            var blockSettings = sprint_editor.getBlockSettings(blockData.name, currentEditorParams);

            var $box = $(sprint_editor.renderTemplate('container-box', {
                uid: uid,
                title: sprint_editor.getBlockTitle(blockData.name),
                compiled: sprint_editor.compileSettings(blockData, blockSettings)
            }));

            $container.append($box);

            var $elBlock = $box.children('.sp-acc-box-block');
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


            var $buttonsBox = $box.children('.sp-x-buttons-box');

            $buttonsBox.on('click', '.sp-acc-box-del', function (e) {
                e.preventDefault();
                var $target = $(this).closest('.sp-acc-box');

                var uid = $target.data('uid');
                sprint_editor.beforeDelete(uid);

                $target.hide(250, function () {
                    $target.remove();
                });
            });

            $buttonsBox.on('click', '.sp-acc-box-up', function (e) {
                e.preventDefault();
                var $block = $(this).closest('.sp-acc-box');
                var $nblock = $block.prev('.sp-acc-box');
                if ($nblock.length > 0) {
                    $block.insertBefore($nblock);
                    sprint_editor.afterSort($block.data('uid'));
                }
            });

            $buttonsBox.on('click', '.sp-acc-box-dn', function (e) {
                e.preventDefault();
                var $block = $(this).closest('.sp-acc-box');
                var $nblock = $block.next('.sp-acc-box');
                if ($nblock.length > 0) {
                    $block.insertAfter($nblock);
                    sprint_editor.afterSort(
                        $block.data('uid')
                    );
                }
            });
        }
    };
});
