sprint_editor.registerBlock('slideshare', function ($, $el, data) {

    data = $.extend({
        url: '',
        embed_url: '',
        width: '510',
        height: '420'
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.url = $el.find('.sp-url').val();
        data.width = $el.find('.sp-width').val();
        data.height = $el.find('.sp-height').val();
        return data;
    };

    this.afterRender = function () {
        var $preview = $el.find('.sp-preview');
        var $input = $el.find('.sp-url');

        if (data.embed_url) {
            renderPreview($preview, data.embed_url)
        } else {
            getPost($input, $preview);
        }

        $input.bindWithDelay('input', function () {
            getPost($input, $preview);
        }, 500);
    };

    function getPost($input, $preview) {
        $preview.empty();

        var inputUrl = $input.val();
        if (inputUrl.length > 0) {
            $.ajax({
                url: "http://www.slideshare.net/api/oembed/2",
                data: {
                    url: inputUrl,
                    format: 'jsonp'
                },
                dataType: "jsonp",
                success: function (result) {
                    if (result && result.html) {

                        var embedUrl = $('<div>' + result.html + '</div>').find('iframe').attr('src');

                        if (embedUrl && embedUrl.length > 0) {
                            data.embed_url = embedUrl;
                            renderPreview($preview, embedUrl);
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                }
            });
        }
    }

    function renderPreview($preview, embedUrl) {
        $preview.html(
            '<iframe width="320" height="180" src="' + embedUrl + '" frameborder="0" allowfullscreen></iframe>'
        );
    }

});
