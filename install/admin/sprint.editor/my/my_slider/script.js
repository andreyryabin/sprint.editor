sprint_editor.registerBlock('my_slider', function ($, $el, data) {
    var areas = [
        {
            dataKey: 'slider',
            blockName: 'my_gallery',
            container: '.gallery'
        }
    ];
    this.getData = function () {
        return data;
    };
    this.collectData = function () {
        return data;
    };
    this.getAreas = function () {
        return areas;
    };
    this.afterRender = function () {

    }
});
