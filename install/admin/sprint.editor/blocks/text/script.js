sprint_editor.registerBlock('text', function ($, $el, data, settings) {

        data = $.extend({
            value: ''
        }, data);

        //var parser = new HtmlWhitelistedSanitizer();

        this.getData = function () {
            // data.value = parser.sanitizeString(
            //     escapeHtml(data.value)
            // );
            return data;
        };

        this.collectData = function () {
            if (!$.fn.trumbowyg) {
                return data;
            }

            // data.value = parser.sanitizeString(
            //     $el.find('.sp-text').val()
            // );

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
                    ['myCss'],
                    ['strong', 'em', 'underline', 'del'],
                    ['link'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight'],
                    ['unorderedList', 'orderedList'],
                    ['removeformat']
                ];

            } else {
                btns = [
                    ['viewHTML'],
                    ['strong', 'em', 'underline', 'del'],
                    ['link'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight'],
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


        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return text.replace(/[&<>"']/g, function (m) {
                return map[m];
            });
        }
    }
);