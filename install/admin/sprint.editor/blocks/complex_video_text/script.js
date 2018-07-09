sprint_editor.registerBlock('complex_video_text', function ($, $el, data) {
    var areas = [
        {
            dataKey : 'video',
            blockName: 'video',
            container : '.sp-area1'
        },
        {
            dataKey : 'preview',
            blockName: 'image_simple',
            container : '.sp-area1-image_simple'
        },
        {
            dataKey : 'text',
            blockName: 'text',
            container : '.sp-area2'
        }
    ];

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.getAreas = function(){
        return areas;
    };
});