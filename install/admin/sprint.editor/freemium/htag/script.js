sprint_editor.registerBlock('htag', function($, $el, data) {

    data = $.extend({
        type: 'h1',
        value: '',
        anchor: ''
    }, data);

    var types = [
        {
            value: 'h1',
            selected: false
        },
        {
            value: 'h2',
            selected: false
        },
        {
            value: 'h3',
            selected: false
        },
        {
            value: 'h4',
            selected: false
        },
        {
            value: 'h5',
            selected: false
        }

    ];

    $.each(types, function (index, item) {
        if (item.value == data.type) {
            item.selected = true;
        }
    });

    this.getData = function () {
        data['types'] = types;
        return data;
    };

    this.collectData = function () {
        data.value = $el.find('input[type=text]').val();
        data.type = $el.find('select').val();
        if (data.value){
            data.anchor = translite(data.value);
        } else {
            data.anchor = '';
        }
        delete data['types'];
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
            max_len : 100,
            change_case : 'L',
            replace_space : '-',
            replace_other : '-',
            delete_repeat_replace : true,
            use_google : false
        });
    }

});