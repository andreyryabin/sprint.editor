sprint_editor.registerBlock('properties', function($, $el, data) {

    data = $.extend({
        elements: [{title: '', text:''},{title: '', text:''}]
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        var trimed = [];
        $el.find('.j-item').each(function(){

            var title = $.trim(
                $(this).find('.j-item-title').val()
            );
            var text = $.trim(
                $(this).find('.j-item-text').val()
            );

            if (title) {
                trimed.push({
                    title : title,
                    text : text
                });
            }
        });

        data.elements = trimed;
        return data;
    };

    this.afterRender = function () {
        var $res = $el.find('.j-properties-result');


        $res.html(
            sprint_editor.renderTemplate('properties-items', data)
        );

        $el.on('click', '.j-properties-add', function(){
            $res.append(
                sprint_editor.renderTemplate('properties-items', {
                    elements: [
                        {title: '',text: ''}
                    ]
                })
            );

        });
    }


});