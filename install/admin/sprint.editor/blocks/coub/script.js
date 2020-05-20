sprint_editor.registerBlock('coub', function ($, $el, data) {

    data = $.extend({
        url: '',
        width: '420',
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
        var $input = $el.find('.sp-url');
        var $preview = $el.find('.sp-preview');

        getPreview($preview, $input);

        $input.bindWithDelay('input', function () {
            getPreview($preview, $(this));
        }, 500);
    };

    function getPreview($preview, $input) {
        var inputUrl = $input.val();
        var regExp = /^.*coub.com\/view\/(\w+)/;
        var match = inputUrl.match(regExp);
        var coubCode = (match && match[1]) ? match[1] : '';

        if (coubCode) {
            $preview.html(
                '<iframe src="//coub.com/embed/' + coubCode + '?muted=false&autostart=false&originalSize=false&startWithHD=false" allowfullscreen="true" frameborder="0" width="320" height="180"></iframe>'
            );
        } else {
            $preview.html('');
        }
    }


});
