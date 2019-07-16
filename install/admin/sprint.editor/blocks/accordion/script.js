sprint_editor.registerBlock('accordion', function ($, $el, data) {

    data = $.extend({
        items: [],
    }, data);


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
                tab.blocks.push(sprint_editor.collectData(
                    $(this).data('uid')
                ))
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


        $el.on('click', '.sp-acc-add-tab', function () {
            addTab({
                title: '',
                blocks: []
            });
        });

        function addTab(tabData) {
            var $tab = $(sprint_editor.renderTemplate('accordion-tab', {
                title: tabData.title
            }));

            $el.find('.sp-acc-container').append($tab);

            var $tabcontainer = $tab.find('.sp-acc-tab-container');

            $.each(tabData.blocks, function (index, item) {
                addblock(item, $tabcontainer)
            });

        }

        function addblock(blockData, $tabcontainer) {
            var uid = sprint_editor.makeUid('sp-acc');
            var $box = $(sprint_editor.renderTemplate('accordion-box', {
                uid: uid
            }));

            $tabcontainer.append($box);

            sprint_editor.registerEntry(uid, sprint_editor.initblock(
                $,
                $box.find('.sp-acc-box-block'),
                blockData.name,
                blockData
            ));
        }
    };
});