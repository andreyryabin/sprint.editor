sprint_editor.registerBlock('text', function ($, $el, data) {

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

        $el.find('.sp-text').trumbowyg({
            svgPath: '/bitrix/admin/sprint.editor/assets/trumbowyg/ui/icons.svg',
            lang: 'ru',
            resetCss: true,
            removeformatPasted: true,
            btns: [
                ['viewHTML'],
                ['myCss'],
                ['strong', 'em', 'underline', 'del'],
                ['link'],
                ['removeformat'],

                ['justifyLeft', 'justifyCenter', 'justifyRight'],
                ['unorderedList', 'orderedList']
            ],

            autogrow: true

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
});