sprint_editor.registerBlock('text', function ($, $el, data, settings) {

        settings = settings || {};

        data = $.extend({
            value: ''
        }, data);

        this.getData = function () {
            return data;
        };

        this.collectData = function () {
            if (!$.fn.trumbowyg) {
                return data;
            }

            data.value = $el.find('.sp-text').val();

            return data;
        };

        this.afterRender = function () {

            if (!$.fn.trumbowyg) {
                return false;
            }

            var btns = [];
            var cssList = {};
            var plugins = {};

            if (settings.csslist && settings.csslist.value) {
                cssList = settings.csslist.value;

                plugins = {
                    mycss: {
                        cssList: cssList
                    }
                };

                btns = [
                    ['viewHTML'],
                    ['formatting'],
                    ['myCss'],
                    ['strong', 'em', 'underline', 'del'],
                    ['link','specialChars'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['removeformat']
                ];

            } else {
                btns = [
                    ['viewHTML'],
                    ['formatting'],
                    ['strong', 'em', 'underline', 'del'],
                    ['link','specialChars'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['removeformat']
                ]
            }


            $el.find('.sp-text').trumbowyg({
                svgPath: '/bitrix/admin/sprint.editor/assets/trumbowyg/ui/icons.svg',
                lang: 'ru',
                resetCss: true,
                removeformatPasted: true,
                autogrow: true,
                btns: btns,
                plugins: plugins
            });

        };
    }
);
