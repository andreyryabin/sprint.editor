sprint_editor.registerBlock('twitter', function ($, $el, data) {

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

        getTweet($input, $preview);

        $input.bindWithDelay('input', function () {
            getTweet($input, $preview);
        }, 500);
    };

    function getTweet($input, $preview) {
        $preview.addClass('sp-empty').empty();
        var inputUrl = $input.val();
        if (inputUrl.length > 0) {
            $.ajax({
                url: "https://publish.twitter.com/oembed",
                data: {
                    url: inputUrl
                },
                dataType: "jsonp",
                success: function (result) {
                    if (result && result.html) {
                        $preview.removeClass('sp-empty').html(result.html);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                }
            });
        }
    }

});
