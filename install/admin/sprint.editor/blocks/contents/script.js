sprint_editor.registerBlock('contents', function ($, $el, data, settings) {

    data = $.extend({
        selectors: [],
        elements: [] /* */
    }, data);

    var all = ['h1', 'h2', 'h3', 'h4', 'h5'];

    if (settings.taglist && settings.taglist.value) {
        all = settings.taglist;
    }


    var checked = [];
    $.each(all, function (index, val) {
        var selected = ($.inArray(val, data.selectors) >= 0);
        checked.push({
            id: val,
            title: val,
            selected: selected
        })

    });
    this.getData = function () {
        data['checked'] = checked;
        return data;
    };

    this.collectData = function () {
        data.selectors = [];

        $el.find('input[type=checkbox]').each(function () {
            var $obj = $(this);
            if ($obj.is(':checked')) {
                var val = $obj.val();
                if (jQuery.inArray(val, all) >= 0) {
                    data.selectors.push($obj.val());
                }
            }
        });


        delete data['checked'];
        return data;
    };

    this.afterRender = function () {

    };

});
