sprint_editor.registerBlock('medialib_collections', function ($, $el, data) {

    data = $.extend({
        collections: []
    }, data);


    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.collections = [];
        $el.find('input[type=checkbox]').each(function () {
            var $obj = $(this);
            if ($obj.is(':checked')) {
                var val = intval($obj.val());
                if (val > 0) {
                    data.collections.push(val);
                }
            }
        });

        return data;
    };

    this.afterRender = function () {
        $.ajax({
            url: sprint_editor.getBlockWebPath('medialib_collections') + '/ajax.php',
            type: 'post',
            data: {
                collections: data.collections
            },
            dataType: 'json',
            success: function (result) {
                $el.find('.sp-medialib-result').html(
                    sprint_editor.renderTemplate('medialib_collections-items', result)
                );
            },
            error: function () {

            }
        });
    };

    var intval = function (val) {
        val = (val) ? val : 0;
        val = parseInt(val, 10);
        return isNaN(val) ? 0 : val;
    };

});