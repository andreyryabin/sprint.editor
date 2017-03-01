sprint_editor.registerBlock('lists', function($, $el, data) {

    data = $.extend({
        elements: [{text:''},{text:''}]
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        var trimed = [];
        $el.find('.j-item-text').each(function(){
            var text = $.trim(
                $(this).val()
            );

            if (text) {
                trimed.push({
                    text : text
                });
            }
        });

        data.elements = trimed;
        return data;
    };

    this.afterRender = function () {
        var $res = $el.find('.j-lists-result');

        $res.html(
            sprint_editor.renderTemplate('lists-items', data)
        );

        $el.on('click', '.j-lists-add', function(){
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