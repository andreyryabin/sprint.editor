sprint_editor.registerBlock('schema_pubdate', function ($, $el, data) {
    data = $.extend({

    }, data);

    var areas = [
        {
            dataKey : 'pubdate',
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