sprint_editor.registerBlock('my_properties', function($, $el, data) {

    data = $.extend({
        elements: [{title: '', text:''},{title: '', text:''}]
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        var trimed = [];
        $el.find('.sp-item').each(function(){

            var title = $.trim(
                $(this).find('.sp-item-title').val()
            );
            var text = $.trim(
                $(this).find('.sp-item-text').val()
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
        var $res = $el.find('.sp-properties-result');


        $res.html(
            sprint_editor.renderTemplate('my_properties-items', data)
        );

        $el.on('click', '.sp-properties-add', function(){
            $res.append(
                sprint_editor.renderTemplate('my_properties-items', {
                    elements: [
                        {title: '',text: ''}
                    ]
                })
            );

        });
    }


});