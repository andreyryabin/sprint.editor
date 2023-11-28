sprint_editor.registerBlock('iblock_sections', function ($, $el, data, settings) {

    settings = settings || {};

    var enabled_iblocks = [];
    if (settings.enabled_iblocks && settings.enabled_iblocks.value && Array.isArray(settings.enabled_iblocks.value)) {
        enabled_iblocks = settings.enabled_iblocks.value;
    }

    var multiple = true;
    if (settings.hasOwnProperty('multiple') && settings.multiple.hasOwnProperty('value')) {
        multiple = !!settings.multiple.value;
    }

    data = $.extend({
        iblock_id: 0,
        section_ids: []
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.iblock_id = findIblockId();
        data.section_ids = findElementIds();
        return data;
    };

    this.afterRender = function () {

        var popupIds = [];

        var uid = sprint_editor.makeUid();
        window[uid] = {
            AddValue: function (newid) {
                newid = intval(newid);
                if (newid > 0) {
                    popupIds.push(newid);
                }
            },

            Complete: function () {
                var oldids = [];
                if (multiple) {
                    oldids = findElementIds();
                }

                sendrequest({
                    iblock_id: findIblockId(),
                    section_ids: $.merge(oldids, popupIds),
                    enabled_iblocks: enabled_iblocks
                });

                popupIds = [];
            }
        };


        $el.on('click', '.sp-open', function () {
            var iblockId = findIblockId();
            if (iblockId > 0) {

                var width = 900;
                var height = 700;
                var url = '/bitrix/admin/iblock_section_search.php?' + decodeURIComponent($.param({
                    lang: 'ru',
                    IBLOCK_ID: iblockId,
                    iblockfix: 'y',
                    lookup: uid,
                    m: multiple ? 'y' : 'n'
                }));


                var w = $(window).width(), h = $(window).height();
                var sizes = '';

                sizes += 'status=no,scrollbars=yes,resizable=yes,';
                sizes += 'width=' + width + ',height=' + height;
                sizes += +',top=' + Math.floor((h - height) / 2 - 14) + ',left=' + Math.floor((w - width) / 2 - 5);

                var popup = window.open(url, '', sizes);

                $(popup).unload(function () {
                    window[uid].Complete();
                });

            }
        });

        $el.on('change', '.sp-select-iblock', function () {
            sendrequest({
                iblock_id: findIblockId(),
                section_ids: findElementIds(),
                enabled_iblocks: enabled_iblocks
            });
        });

        sendrequest({
            iblock_id: data.iblock_id,
            section_ids: data.section_ids,
            enabled_iblocks: enabled_iblocks
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

        var $jresult = $el.find('.sp-result');

        $.ajax({
            url: sprint_editor.getBlockWebPath('iblock_sections') + '/ajax.php',
            type: 'post',
            data: requestParams,
            dataType: 'json',
            success: function (result) {

                $jresult.html(
                    sprint_editor.renderTemplate('iblock_sections-select', result)
                );

                var $elem = $jresult.find('.sp-elements');

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

                if (callback) {
                    callback();
                }
            },
            error: function () {

            }
        });
    };

});
