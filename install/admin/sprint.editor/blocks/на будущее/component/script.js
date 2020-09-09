sprint_editor.registerBlock('component', function ($, $el, data) {

    data = $.extend({
        component_name: '',
        component_template: '',
        component_params: {},
        filter_site: '',
        filter_include: '',
        filter_exclude: ''
    }, data);

    var pmanager = new BXComponentParamsManager({
        'requestUrl': '/bitrix/admin/fileman_component_params.php',
        'relPath': '/',
        'id': getRandomInt(100, 9999)
    });

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.component_params = pmanager.GetParamsValues();
        data.component_template = pmanager.GetTemplateValue();
        return data;
    };

    this.afterRender = function () {

        var $elFilter = $el.find('.sp-filter');
        var $elParams = $el.find('.sp-result');
        var boxWidth = $el.width();

        boxWidth = boxWidth ? boxWidth : 650;

        renderComponentParams();
        renderFilters(0);

        $elFilter.on('change', '.sp-component', function (e) {
            e.preventDefault();
            var selectedName = $(this).val();
            if (selectedName != data.component_name) {
                data.component_name = selectedName;
                data.component_template = '';
                data.component_params = {};
                renderComponentParams();
            }
        });

        $elFilter.on('change', '.sp-filter-site', function (e) {
            e.preventDefault();
            var selectedName = $(this).val();
            if (selectedName != data.filter_site) {
                data.filter_site = selectedName;
                renderComponentParams();
            }
        });

        $elFilter.on('click', '.sp-reload', function () {
            renderFilters(1);
        });

        function renderComponentParams() {
            showSiteFilter();

            if (!data.component_name) {
                $elParams.removeClass('bxcompprop-wrap').removeAttr('style').empty();
                return;
            }

            BX.onCustomEvent(pmanager, 'OnComponentParamsDisplay', [{
                name: data.component_name,
                template: data.component_template,
                currentValues: data.component_params,
                siteTemplate: data.filter_site,
                container: $elParams.get(0),
                parent: false,
                relPath: '/',
                callback: function (params, container) {

                    BX.onCustomEvent(pmanager, 'OnComponentParamsResize', [
                        boxWidth,
                        400
                    ]);

                    $elParams.find('.bxcompprop-left').hide();

                    $elParams.find('.bxcompprop-right').css({
                        width: boxWidth - 40,
                        top: 0,
                        left: 0
                    });
                }
            }]);
        }

        function renderFilters(clearCache) {
            clearCache = clearCache || 0;

            $.ajax({
                url: sprint_editor.getBlockWebPath('component') + '/ajax.php',
                type: 'post',
                dataType: 'json',
                data: {
                    component_name: data.component_name,
                    filter_site: data.filter_site,
                    filter_include: data.filter_include,
                    filter_exclude: data.filter_exclude,
                    clear_cache: clearCache
                },
                success: function (result) {
                    $elFilter.html(
                        sprint_editor.renderTemplate('component-select', result)
                    );

                    showSiteFilter();
                }
            });
        }

        function showSiteFilter() {
            if (data.component_name) {
                $elFilter.find('.sp-label-site').show();
            } else {
                $elFilter.find('.sp-label-site').hide();
                $elFilter.find('.sp-filter-site').val('');
            }
        }
    };


    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
});
