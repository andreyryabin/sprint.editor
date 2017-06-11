sprint_editor.registerBlock('video', function($, $el, data) {

    data = $.extend({
        url: ''
    }, data);

    var areas = [
        {
            dataKey : 'preview',
            blockName: 'image',
            container : '.sp-image'
        }

    ];

    this.getData = function () {
        return data;
    };

    this.getAreas = function (){
        return areas;
    };

    this.collectData = function () {
        data.url = $el.find('.sp-url').val();
        return data;
    };

    this.afterRender = function () {
        var $input = $el.find('.sp-url');

        getVideo($input.val());
        $input.bindWithDelay('input', function () {
            getVideo($(this).val());
        }, 500);
    };

    function getVideo(youtubeUrl){
        var youtubeCode = '';
        if (youtubeUrl){
            var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            var match = youtubeUrl.match(regExp);
            youtubeCode = (match && match[2].length == 11) ? match[2] : false;
        }

        if (youtubeCode){
            $el.find('.sp-preview').html(
                sprint_editor.renderTemplate('video-iframe', {youtubeCode: youtubeCode})
            );
        } else {
            $el.find('.sp-preview').html('');
        }
    }

});