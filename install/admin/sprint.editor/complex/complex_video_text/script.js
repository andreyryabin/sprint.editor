sprint_editor.registerBlock('complex_video_text', function ($, $el, data) {
    var areas = [
        {
            dataKey : 'video',
            blockName: 'video',
            container : '.j-area1'
        },
        {
            dataKey : 'preview',
            blockName: 'image',
            container : '.j-image'
        },
        {
            dataKey : 'text',
            blockName: 'text',
            container : '.j-area2'
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