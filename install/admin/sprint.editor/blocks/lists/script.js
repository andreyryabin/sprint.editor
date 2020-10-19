sprint_editor.registerBlock('lists', function ($, $el, data, settings) {

    data = $.extend({
        type: 'ul',
        elements: [{text: ''}, {text: ''}]
    }, data);

    this.getData = function () {

        var taglist = [
            {id: 'ol', title: 'Нумерованный'},
            {id: 'ul', title: 'Маркированный'},
        ];

        if (settings.taglist && settings.taglist.value) {
            taglist = [];
            $.each(settings.taglist.value, function (index, val) {
                taglist.push({id: index, title: val})
            });
        }

        data['taglist'] = taglist;

        return data;
    };

    this.collectData = function () {
        var trimed = [];
        $el.find('.sp-item-text').each(function () {
            var text = $.trim(
                $(this).val()
            );

            if (text) {
                trimed.push({
                    text: text
                });
            }
        });
        delete data['taglist'];
        data.type = $el.find('select').val();
        data.elements = trimed;
        return data;
    };

    this.afterRender = function () {
        var $res = $el.find('.sp-lists-result');

        $res.html(
            sprint_editor.renderTemplate('lists-items', data)
        );

        $el.on('click', '.sp-lists-add', function () {
            $res.append(
                sprint_editor.renderTemplate('lists-items', {
                    elements: [
                        {text: ''}
                    ]
                })
            );

        });
    }
});
