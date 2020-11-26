sprint_editor.registerBlock('instagram', function ($, $el, data) {

    data = $.extend({
        url: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data['url'] = $el.find('.sp-url').val();
        return data;
    };

    this.afterRender = function () {
        var $preview = $el.find('.sp-preview');
        var $input = $el.find('.sp-url');

        getPost($input, $preview);

        $input.bindWithDelay('input', function () {
            getPost($input, $preview);
        }, 500);
    };

    function getPost($input, $preview) {
        $preview.addClass('sp-empty').empty();
        var inputUrl = $input.val();
        if (inputUrl.length > 0) {
            $.ajax({
                url: sprint_editor.getBlockWebPath('instagram') + '/ajax.php',
                data: {
                    url: inputUrl
                },
                dataType: "html",
                success: function (result) {
                    if (result) {
                        $preview.removeClass('sp-empty').html(result);
                        if (typeof instgrm !== 'undefined') {
                            instgrm.Embeds.process();
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                }
            });
        }
    }

});
