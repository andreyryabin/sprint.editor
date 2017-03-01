sprint_editor.registerBlock('schema_start', function ($, $el, data) {
    data = $.extend({
        schema: 'Article'
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

});