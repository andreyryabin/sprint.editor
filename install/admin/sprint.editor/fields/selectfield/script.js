sprint_editor.registerBlock('selectfield', function ($, $el, data, settings) {
        data = $.extend({
            value: '',
            options: [],
        }, data);

        if (settings.options && settings.options.value) {
            $.each(settings.options.value, function (index, val) {
                data.options.push({id: index, title: val})
            });
        }

        this.getData = function () {
            return data;
        };

        this.collectData = function () {
            data.value = $el.children('select').val();

            delete data['options'];
            return data;
        };

        this.afterRender = function () {
        };
    }
);
