sprint_editor.registerBlock('video_gallery', function($, $el, data) {

    data = $.extend({
        videos: []
    }, data);

    var videoCollection = [];

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.videos = [];

        $.each(videoCollection, function (index, entry) {
            var videoData = sprint_editor.collectData(entry);
            if (videoData.url){
                data.videos.push(videoData);
            }
        });

        return data;
    };

    this.afterRender = function () {

        $.each(data.videos, function (index, videoData) {
            pushVideo(videoData);
        });

        $el.find('.sp-add').on('click',function(){
            pushVideo({});
        });

    };


    function pushVideo(videoData) {
        $el.find('.sp-video-items').append('<div class="sp-video-item"></div>');

        var $elVideo = $el.find('.sp-video-item').last();
        var entry = sprint_editor.initblock($elVideo, 'video',videoData);

        sprint_editor.initblockAreas($elVideo, entry);

        videoCollection.push(entry);
    }

});