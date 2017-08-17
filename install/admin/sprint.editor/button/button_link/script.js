sprint_editor.registerBlock('button_link', function($, $el, data) {

    data = $.extend({
        title: '',
        url: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.title = $el.find('.sp-title').val();
        data.url = $el.find('.sp-url').val();
        return data;
    };

});