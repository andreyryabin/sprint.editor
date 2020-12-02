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
        data.blocks = [];
        $el.find('.sp-acc-box').each(function () {
            var blockData = sprint_editor.collectData(
                $(this).data('uid')
            );

            blockData.settings = sprint_editor.collectSettings(
                $(this).find('.sp-x-box-settings')
            );

            data.blocks.push(blockData);
        });
        delete data['blocklist'];
        return data;
    };

    this.afterRender = function () {
        var $container = $el.find('.sp-items');

        $.each(data.blocks, function (index, blockData) {
            addblock(blockData, $container);
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

        $el.on('click', '.sp-buttons .sp-x-btn', function () {
            addblock(
                {
                    name: $(this).data('name')
                },
                $container
            );
        });

        $container.sortable({
            items: ".sp-acc-box",
            handle: ".sp-acc-box-handle",
        });

        $el.on('click', '.sp-acc-box-up', function (e) {
            e.preventDefault();
            var $block = $(this).closest('.sp-acc-box');
            var $nblock = $block.prev('.sp-acc-box');
            if ($nblock.length > 0) {
                $block.insertBefore($nblock);
                sprint_editor.afterSort($block.data('uid'));
            }
        });

        $el.on('click', '.sp-acc-box-dn', function (e) {
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

        function addblock(blockData, $container) {
            var uid = sprint_editor.makeUid('sp-acc');
            var blockSettings = sprint_editor.getBlockSettings(blockData.name, currentEditorParams);

            var $box = $(sprint_editor.renderTemplate('container-box', {
                uid: uid,
                title: sprint_editor.getBlockTitle(blockData.name),
                compiled: sprint_editor.compileSettings(blockData, blockSettings)
            }));

            $container.append($box);

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
