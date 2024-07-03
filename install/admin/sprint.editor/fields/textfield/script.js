sprint_editor.registerBlock('textfield', function ($, $el, data, settings) {
        data = $.extend({
            value: '',
            placeholder: 'Текст'
        }, data);


        if (settings.placeholder) {
            data.placeholder = settings.placeholder;
        }

        this.getData = function () {
            return data;
        };

        this.collectData = function () {
            data.value = $el.find('.sp-text').val();

            delete data['placeholder'];

            return data;
        };

        this.afterRender = function () {
        };
    }
);
