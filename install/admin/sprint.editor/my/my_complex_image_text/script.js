sprint_editor.registerBlock('my_complex_image_text', function ($, $el, data) {
    var areas = [
        {
            dataKey: 'image',
            blockName: 'my_image',
            container: '.sp-area1'
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
