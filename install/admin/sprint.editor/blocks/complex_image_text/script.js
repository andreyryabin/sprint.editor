sprint_editor.registerBlock('complex_image_text', function ($, $el, data) {
    var areas = [
        {
            dataKey: 'image',
            blockName: 'image',
            container: '.sp-area1'
        },
        {
            dataKey: 'text',
            blockName: 'text',
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
