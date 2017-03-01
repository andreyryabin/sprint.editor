sprint_editor.registerBlock('schema_image', function ($, $el, data) {
    var areas = [
        {
            dataKey : 'image',
            blockName: 'image',
            container : '.j-area1'
        }

    ];

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.getAreas = function (){
        return areas;
    };

});