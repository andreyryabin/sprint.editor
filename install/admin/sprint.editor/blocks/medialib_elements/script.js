sprint_editor.registerBlock('medialib_elements', function ($, $el, data) {

    data = $.extend({
        medialib_type: 'image',
        element_ids: []
    }, data);

    var navparams = {
        page_left: 1,
        page_right: 1,
        page_num: 1
    };

    if (data.page_num && data.page_num >= 1) {
        navparams.page_num = data.page_num;
    }

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.element_ids = findElementIds();
        data.medialib_type = findMedialibType();
        return data;
    };

    this.afterRender = function () {
        $el.on('change', '.sp-select-collection', function () {
            sendrequest({
                medialib_type: findMedialibType(),
                collection_id: findCollectionId(),
                element_ids: findElementIds(),
                page: 1,
            });
        });
        $el.on('change', '.sp-select-types', function () {
            let mtype = findMedialibType();
            let elems = mtype === data.medialib_type ? data.element_ids : [];

            sendrequest({
                medialib_type: mtype,
                collection_id: 0,
                element_ids: elems,
                page: 1,
            });
        });

        $el.on('click', '.sp-nav-left', function () {
            sendrequest({
                medialib_type: findMedialibType(),
                collection_id: findCollectionId(),
                element_ids: findElementIds(),
                page: navparams.page_left
            });
        });

        $el.on('click', '.sp-nav-right', function () {
            sendrequest({
                medialib_type: findMedialibType(),
                collection_id: findCollectionId(),
                element_ids: findElementIds(),
                page: navparams.page_right
            });
        });

        $el.on('click', '.sp-item-del', function () {
            $(this).closest('.sp-item').remove();
        });
        sendrequest({
            medialib_type: data.medialib_type,
            element_ids: data.element_ids,
        });

    };

    var findCollectionId = function () {
        return $el.find('.sp-select-collection').val();
    };

    var findMedialibType = function () {
        return $el.find('.sp-select-types').val();
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

    var intval = function (val) {
        val = (val) ? val : 0;
        val = parseInt(val, 10);
        return isNaN(val) ? 0 : val;
    };

    var sendrequest = function (requestParams) {
        var $jresult = $el.find('.sp-medialib-result');

        $.ajax({
            url: sprint_editor.getBlockWebPath('medialib_elements') + '/ajax.php',
            type: 'post',
            data: Object.assign({
                sessid: BX.bitrix_sessid()
            }, requestParams),
            dataType: 'json',
            success: function (result) {
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

                let tpl = '';
                if (result.medialib_type === 'image') {
                    tpl = 'medialib_elements-images';
                    $el.addClass('type-image')
                } else {
                    tpl = 'medialib_elements-elements';
                    $el.removeClass('type-image')
                }

                $jresult.html(
                    sprint_editor.renderTemplate('medialib_elements-select', $.extend({
                        source_html: sprint_editor.renderTemplate(tpl, {elements: result.source, show_del: false}),
                        elements_html: sprint_editor.renderTemplate(tpl, {elements: result.elements, show_del: true}),
                    }, result))
                );

                var $elem = $jresult.find('.sp-elements');
                var $sour = $jresult.find('.sp-source');

                var removeIntent = false;
                $elem.sortable({
                    items: ".sp-item",
                    placeholder: "sp-placeholder",
                    over: function () {
                        removeIntent = false;
                    },
                    out: function () {
                        removeIntent = true;
                    },
                    beforeStop: function (event, ui) {
                        if (removeIntent) {
                            ui.item.remove();
                        } else {
                            ui.item.removeAttr('style');
                        }

                    },
                    receive: function (event, ui) {
                        var uiIndex = ui.item.attr('data-id');
                        var item = $(this).find('[data-id=' + uiIndex + ']');
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
