sprint_editor.registerBlock('video', function($, $el, data) {

    data = $.extend({
        url: ''
    }, data);

    var areas = [
        {
            dataKey : 'preview',
            blockName: 'image',
            container : '.j-image'
        }

    ];

    this.getData = function () {
        return data;
    };

    this.getAreas = function (){
        return areas;
    };

    this.collectData = function () {
        data.url = $el.find('.j-url').val();
        return data;
    };

    this.afterRender = function () {
        var input = $el.find('.j-url');

        getVideo(input);
        input.bindWithDelay('input', function () {
            getVideo(input);
        }, 500);
    };

    function getVideo(input){
        var inputUrl = input.val();

        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = inputUrl.match(regExp);
        var youtubeCode = (match && match[2].length == 11) ? match[2] : false;


        if (youtubeCode){
            $el.find('.j-preview').html(
                '<iframe width="320" height="180" src="http://www.youtube.com/embed/' + youtubeCode + '?rel=0" frameborder="0" allowfullscreen></iframe>'
            );
        } else {
            $el.find('.j-preview').html('');
        }
    }

});