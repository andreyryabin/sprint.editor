sprint_editor.registerBlock('button_link', function ($, $el, data) {

    data = $.extend({
        title: '',
        url: '',
        target: ''
    }, data);

    var targets = [
        {
            name: 'Открыть страницу',
            value: '',
            selected: false
        },
        {
            name: 'Открыть страницу в новой вкладке',
            value: '_blank',
            selected: false
        }

    ];

    $.each(targets, function (index, item) {
        if (item.value == data.target) {
            item.selected = true;
        }
    });

    this.getData = function () {
        data['targets'] = targets;
        return data;
    };

    this.collectData = function () {
        data.title = $el.find('.sp-title').val();
        data.url = $el.find('.sp-url').val();
        data.target = $el.find('.sp-target').val();

        delete data['targets'];
        return data;
    };


});