sprint_editor.registerBlock('dump', function ($, $el, data) {
    this.getData = function () {
        console.log(data);
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.afterRender = function () {
    };

});