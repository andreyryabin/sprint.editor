sprint_editor.registerBlock('htag', function($, $el, data) {

    data = $.extend({
        type: 'h1',
        value: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.value = $el.find('input[type=text]').val();
        data.type = $el.find('select').val();
        return data;
    };

    this.afterRender = function () {
    };

});