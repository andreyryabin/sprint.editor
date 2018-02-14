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

    var searchId = sprint_editor.makeUid('sp');

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.iblock_id = findIblockId();
        data.element_ids = findElementIds();
        return data;
    };

    this.afterRender = function () {
        $el.on('click', '.sp-open', function () {
            var iblockId = findIblockId();
            if (iblockId > 0) {
                var url = '/bitrix/admin/iblock_element_search.php?' + decodeURIComponent($.param({
                    lang: 'ru',
                    IBLOCK_ID: iblockId,
                    n: searchId,
                    iblockfix: 'y'
                }));

                jsUtils.OpenWindow(url, 900, 700);
            }
        });

        $el.on('change', '#' + searchId, function () {
            var newid = intval($(this).val());
            if (newid > 0) {

                var ids = findElementIds();
                ids.push(newid);

                sendrequest({
                    iblock_id: findIblockId(),
                    element_ids: ids,
                    page: navparams.page_num,
                    name: $el.find('.sp-filter-name').val(),
                    id1: $el.find('.sp-filter-id1').val(),
                    id2: $el.find('.sp-filter-id2').val()
                });
            }
        });

        $el.on('click', '.sp-toggle', function () {
            if ($el.hasClass('sp-show')) {
                $el.find('.sp-source,.sp-filter').hide(250);
                $el.removeClass('sp-show');
            } else {
                $el.find('.sp-source,.sp-filter').show(250);
                $el.addClass('sp-show');
            }
        });

        $el.on('keypress', 'input', function (e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                var focusclass = $el.find('input:focus').attr('class');
                focusclass = '.' + focusclass.split(' ').join('.');

                sendrequest({
                    iblock_id: findIblockId(),
                    element_ids: findElementIds(),
                    page: 1,
                    name: $el.find('.sp-filter-name').val(),
                    id1: $el.find('.sp-filter-id1').val(),
                    id2: $el.find('.sp-filter-id2').val()
                }, function () {
                    var $input = $el.find(focusclass);
                    var tval = $input.val();
                    $input.focus().val('').val(tval);

                });
            }
        });

        $el.on('click', '.sp-filter-subm', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                page: 1,
                name: $el.find('.sp-filter-name').val(),
                id1: $el.find('.sp-filter-id1').val(),
                id2: $el.find('.sp-filter-id2').val()
            });

        });

        $el.on('change', '.sp-select-iblock', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                page: 1,
                name: '',
                id1: '',
                id2: ''
            });
        });

        $el.on('click', '.sp-nav-left', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                page: navparams.page_left,
                name: $el.find('.sp-filter-name').val(),
                id1: $el.find('.sp-filter-id1').val(),
                id2: $el.find('.sp-filter-id2').val()
            });
        });

        $el.on('click', '.sp-nav-right', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                page: navparams.page_right,
                name: $el.find('.sp-filter-name').val(),
                id1: $el.find('.sp-filter-id1').val(),
                id2: $el.find('.sp-filter-id2').val()
            });
        });

        sendrequest({
            iblock_id: data.iblock_id,
            element_ids: data.element_ids,
            page: navparams.page_num,
            name: $el.find('.sp-filter-name').val(),
            id1: $el.find('.sp-filter-id1').val(),
            id2: $el.find('.sp-filter-id2').val()
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

    var intval = function (val) {
        val = (val) ? val : 0;
        val = parseInt(val, 10);
        return isNaN(val) ? 0 : val;
    };


    var sendrequest = function (requestParams, callback) {

        var $jresult = $el.find('.sp-iblock-result');

        $.ajax({
            url: sprint_editor.getBlockWebPath('iblock_elements') + '/ajax.php',
            type: 'post',
            data: requestParams,
            dataType: 'json',
            success: function (result) {
                result.page_num = intval(result.page_num);
                result.page_cnt = intval(result.page_cnt);

                result.searchId = searchId;

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

                if (result.iblock_id > 0){
                    $el.find('.sp-open').show();
                } else {
                    $el.find('.sp-open').hide();
                }

                $jresult.html(
                    sprint_editor.renderTemplate('iblock_elements-select', result)
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

                if (callback) {
                    callback();
                }
            },
            error: function () {

            }
        });
    };

});