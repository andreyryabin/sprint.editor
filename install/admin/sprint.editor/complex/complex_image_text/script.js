sprint_editor.registerBlock('complex_image_text', function ($, $el, data) {
    var areas = [
    {
        "blockName": "image",
        "dataKey": "image",
        "container": ".sp-area-1"
    },
    {
        "blockName": "text",
        "dataKey": "text",
        "container": ".sp-area-2"
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
