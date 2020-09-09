sprint_editor.registerBlock('htag', function ($, $el, data, settings) {

    settings = settings || {};

    data = $.extend({
        type: 'h1',
        value: '',
        anchor: ''
    }, data);


    this.getData = function () {
        var taglist = [
            {id: 'h1', title: 'h1'},
            {id: 'h2', title: 'h2'},
            {id: 'h3', title: 'h3'},
            {id: 'h4', title: 'h4'},
            {id: 'h5', title: 'h5'},
        ];

        if (settings.taglist && settings.taglist.value) {
            taglist = [];
            $.each(settings.taglist.value, function (index, val) {
                taglist.push({id: index, title: val})
            });
        }

        data['taglist'] = taglist;

        return data;
    };

    this.collectData = function () {
        data.value = $el.find('input[type=text]').val();
        data.type = $el.find('select').val();
        if (data.value) {
            data.anchor = translite(data.value);
        } else {
            data.anchor = '';
        }
        delete data['taglist'];
        return data;
    };

    this.afterRender = function () {
        var $input = $el.find('input[type=text]');
        var $anchor = $el.find('.sp-anchor');

        $input.bindWithDelay('input', function () {
            $anchor.text(translite(
                $(this).val()
            ));
        }, 500);

    };

    function translite(val) {
        return BX.translit(val, {
            max_len: 100,
            change_case: 'L',
            replace_space: '-',
            replace_other: '-',
            delete_repeat_replace: true,
            use_google: false
        });
    }

});
