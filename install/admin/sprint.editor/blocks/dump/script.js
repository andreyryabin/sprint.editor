sprint_editor.registerBlock('dump', function ($, $el, data) {
    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        if (data.nameold != ''){
            data.name = data.nameold;
            delete data.nameold;
        };
        return data;
    };

    this.afterRender = function () {
        $el.prepend(sprint_editor.renderTemplate('box-remote-block',data));
    };

});