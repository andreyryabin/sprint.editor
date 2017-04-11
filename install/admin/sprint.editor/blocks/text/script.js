sprint_editor.registerBlock('text', function ($, $el, data) {

    data = $.extend({
        value: ''
    }, data);

    var parser = new HtmlWhitelistedSanitizer();

    this.getData = function () {
        data.value = parser.sanitizeString(
            escapeHtml(data.value)
        );
        return data;
    };

    this.collectData = function () {
        data.value = parser.sanitizeString(
            $el.find('textarea').val()
        );
        return data;
    };

    this.afterRender = function () {
        var $textarea = $el.find('textarea');
        if ($.fn.trumbowyg) {
            $textarea.trumbowyg({
                svgPath: '/bitrix/admin/sprint.editor/assets/trumbowyg/ui/icons.svg',
                lang: 'ru',
                resetCss: true,
                removeformatPasted: true,
                btns: [
                    ['viewHTML'],
                    ['bold', 'italic', 'underline', 'strikethrough'],
                    ['link'],
                    ['removeformat']
                ],

                autogrow: true
            });
        } else {
            $textarea.css({overflow: 'hidden'});
            $textarea.height(70); // min-height
            $textarea.height($textarea.prop('scrollHeight'));
            $textarea.keyup(function (e) {
                $(this).height(70); // min-height
                $(this).height(this.scrollHeight);
            });
        }
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
});