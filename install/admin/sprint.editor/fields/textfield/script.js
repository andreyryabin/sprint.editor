sprint_editor.registerBlock('textfield', function ($, $el, data, settings) {
        data = $.extend({
            value: '',
            placeholder: 'Текст'
        }, data);

        if (settings.default && data.value === '') {
            data.value = settings.default.value;
        }

        if (settings.placeholder) {
            data.placeholder = settings.placeholder.value;
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
