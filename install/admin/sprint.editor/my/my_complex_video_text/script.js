sprint_editor.registerBlock('my_complex_video_text', function ($, $el, data) {
    var areas = [
        {
            dataKey: 'video',
            blockName: 'my_video',
            container: '.sp-area1'
        },
        {
            dataKey: 'preview',
            blockName: 'my_image',
            container: '.sp-area1-image'
        },
        {
            dataKey: 'text',
            blockName: 'my_text',
            container: '.sp-area2'
        }
    ];

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.getAreas = function () {
        return areas;
    };
});
