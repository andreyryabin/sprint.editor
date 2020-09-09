sprint_editor.registerBlock('my_text', function ($, $el, data, settings) {

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
            };
            
            var $text;
            
            $text = $el.find('.sp-text').val();

            /*убираем span появляющиеся из-за word*/
            $text = $text.replace(/<span.+?>.+?>/gi, '');

            /* убираем пустые теги   */
            $text = $text.replace(/<[^\/>][^>]*><\/[^>]+>/igm, '');


            /* удаляем атрибуты */
            //$text = $text.replace(/\w+(?<!title|src|style|href|target)=((".+?")|('.+?'))/igm, '');

            data.value = $text;
            return data;
        };

        this.afterRender = function () {
            $el.find('.sp-text').trumbowyg({
                lang: 'ru',
                //resetCss: true, //стиль страницы  НЕ влиял на внешний вид текста в редакторе
                defaultLinkTarget: '_blank', //Разрешить устанавливать целевое значение атрибута ссылки
                minimalLinks: true, //Уменьшите наложение ссылок, чтобы использовать только поля urlи text
                tagsToRemove: ['script', 'link', 'iframe', 'input','br','script'], //очистить код, удалив все теги, которые вы хотите
                removeformatPasted: true, //чтобы стили Не вставлялись из буфера обмена
                semantic: true, //Создает лучший, более семантически ориентированный HTML
                changeActiveDropdownIcon: true, // выпадающее меню изменится на значок активной подкнопки
                btns: [ //выбирать кнопки, отображаемые на панели кнопок
                    ['viewHTML'],
                    ['undo', 'redo'],
                    ['p','blockquote'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link', 'upload','noembed'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    //['unorderedList', 'orderedList'],
                    ['removeformat'],
                    ['fullscreen'],

                ],
                plugins: {
                }
            });
        }
    }
);