sprint_editor.registerBlock('video', function($, $el, data) {

    data = $.extend({
        url: '',
        width: '100%',
        height: '480'
    }, data);

    var areas = [
        {
            dataKey : 'preview',
            blockName: 'image',
            container : '.sp-area1-image'
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
        data.width = $el.find('.sp-width').val();
        data.height = $el.find('.sp-height').val();
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