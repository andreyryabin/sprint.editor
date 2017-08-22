sprint_editor.registerBlock('iblock_elements', function ($, $el, data) {

    data = $.extend({
        iblock_id: 0,
        element_ids: []
    }, data);

    var navparams = {
        page_left: 1,
        page_right: 1,
        page_num: 1
    };

    if (data.page_num && data.page_num >= 1){
        navparams.page_num = data.page_num;
    }

    var hideSource = 1;
    /*if (data.element_ids.length <=0 ){
        hideSource = 0;
    }*/

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.iblock_id = findIblockId();
        data.element_ids = findElementIds();
        data.page_num = navparams.page_num;
        return data;
    };

    this.afterRender = function () {
        $el.on('click','.sp-toggle', function(){
            if (hideSource){
                $el.find('.sp-source,.sp-filter').show(250);
                hideSource = 0
            } else {
                $el.find('.sp-source,.sp-filter').hide(250);
                hideSource = 1;
            }
        });

        $el.on('change', '.sp-select-iblock', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                page: 1
            });
        });

        $el.on('click', '.sp-nav-left', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                page: navparams.page_left
            });
        });

        $el.on('click', '.sp-nav-right', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                page: navparams.page_right
            });
        });

        sendrequest({
            iblock_id: data.iblock_id,
            element_ids: data.element_ids,
            page:navparams.page_num
        });

    };

    var findIblockId = function () {
        return intval(
            $el.find('.sp-select-iblock').val()
        );
    };

    var findElementIds = function () {
        var $obj = $el.find('.sp-elements');

        var values = [];
        $obj.find('.sp-item').each(function () {
            var val = intval(
                $(this).data('id')
            );
            if (val > 0) {
                values.push(val);
            }
        });
        return values;
    };

    var intval = function(val){
        val = (val) ? val : 0;
        val = parseInt(val,10);
        return isNaN(val) ? 0 : val;
    };

    var sendrequest = function (requestParams) {
        var $jresult = $el.find('.sp-iblock-result');

        $.ajax({
            url: sprint_editor.getBlockWebPath('iblock_elements') + '/ajax.php',
            type: 'post',
            data: requestParams,
            dataType: 'json',
            success: function (result) {
                result.hideSource = hideSource;
                result.page_num = intval(result.page_num);
                result.page_cnt = intval(result.page_cnt);

                navparams.page_num = result.page_num;
                if (result.page_num - 1 > 1) {
                    navparams.page_left = result.page_num - 1;
                } else {
                    navparams.page_left = 1;
                }
                if (result.page_num + 1 > result.page_cnt) {
                    navparams.page_right = result.page_cnt;
                } else {
                    navparams.page_right = result.page_num + 1;
                }

                $jresult.html(
                    sprint_editor.renderTemplate('iblock_elements-select', result)
                );

                var $elem = $jresult.find('.sp-elements');
                var $sour = $jresult.find('.sp-source');

                var removeIntent = false;
                $elem.sortable({
                    items: ".sp-item",
                    placeholder:"sp-placeholder",
                    over: function () {
                        removeIntent = false;
                    },
                    out: function () {
                        removeIntent = true;
                    },
                    beforeStop: function (event, ui) {
                        if(removeIntent){
                            ui.item.remove();
                        } else {
                            ui.item.removeAttr('style');
                        }

                    },
                    receive: function (event, ui) {
                        var uiIndex = ui.item.attr('data-id');
                        var item =  $(this).find('[data-id=' + uiIndex + ']');
                        if (item.length > 1) {
                            item.last().remove();
                        }
                    }
                });

                $sour.find('.sp-item').draggable({
                    connectToSortable: $elem,
                    helper: "clone",
                    revert: "invalid"
                  });
            },
            error: function () {

            }
        });
    };

});