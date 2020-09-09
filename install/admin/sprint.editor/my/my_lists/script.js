sprint_editor.registerBlock('my_lists', function ($, $el, data) {

    data = $.extend({
        elements: [{text: ''}, {text: ''}]
    }, data);

    this.getData = function () {
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

        data.elements = trimed;
        return data;
    };

    this.afterRender = function () {
        var $res = $el.find('.sp-lists-result');

        $res.html(
            sprint_editor.renderTemplate('my_lists-items', data)
        );

        $el.on('click', '.sp-lists-add', function () {
            $res.append(
                sprint_editor.renderTemplate('my_lists-items', {
                    elements: [
                        {text: ''}
                    ]
                })
            );
        });
        $res.sortable();
    }
});
