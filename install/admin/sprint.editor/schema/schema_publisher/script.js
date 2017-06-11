sprint_editor.registerBlock('schema_publisher', function ($, $el, data) {
    var areas = [
        {
            dataKey : 'publisher',
            blockName: 'textfield',
            container : '.sp-area1'
        },
        {
            dataKey : 'image',
            blockName: 'image',
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