sprint_editor.registerBlock('instagram', function ($, $el, data) {

    data = $.extend({
        url: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data['url'] = $el.find('.j-url').val();
        return data;
    };

    this.afterRender = function () {
        var $preview = $el.find('.j-preview');
        var $input = $el.find('.j-url');

        getPost($input, $preview);

        $input.bindWithDelay('input', function () {
            getPost($input, $preview);
        }, 500);
    };

    function getPost($input, $preview) {
        $preview.empty();

        var inputUrl = $input.val();
        if (inputUrl.length > 0){
            $.ajax({
                url: "https://api.instagram.com/oembed",
                data: {
                    url: inputUrl
                },
                dataType: "jsonp",
                success: function(result) {
                    if (result && result.html) {
                        $preview.html(result.html);
                        if (instgrm){
                            instgrm.Embeds.process();
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError){
                }
            });
        }
    }

});