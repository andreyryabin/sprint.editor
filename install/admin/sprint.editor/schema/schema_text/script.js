sprint_editor.registerBlock('schema_text', function ($, $el, data) {
    var areas = [
        {
            dataKey : 'article',
            blockName: 'text',
            container : '.sp-area1'
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