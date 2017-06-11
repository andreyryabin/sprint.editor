sprint_editor.registerBlock('schema_headline', function ($, $el, data) {
    var areas = [
        {
            dataKey : 'headline',
            blockName: 'textfield',
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