sprint_editor.registerBlock('schema_publisher', function ($, $el, data) {
    var areas = [
        {
            dataKey : 'publisher',
            blockName: 'textfield',
            container : '.j-area1'
        },
        {
            dataKey : 'image',
            blockName: 'image',
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