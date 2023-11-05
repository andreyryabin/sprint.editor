sprint_editor.registerBlock('complex_video_text', function ($, $el, data) {
    var areas = [
    {
        "blockName": "video",
        "dataKey": "video",
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
