sprint_editor.registerBlock('schema_end', function ($, $el, data) {
    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };
});