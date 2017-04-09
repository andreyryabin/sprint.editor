sprint_editor.registerBlock('component', function ($, $el, data) {

    data = $.extend({
        component_name: '',
        component_template: '',
        component_params: {},
        site_template: ''
    }, data);

    var pmanager = new BXComponentParamsManager({
        'requestUrl': '/bitrix/admin/fileman_component_params.php',
        'relPath': '/',
        'id': getRandomInt()
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

        var $elFilter = $el.find('.j-filter');
        var $elParams = $el.find('.j-result');
        var boxWidth = $el.width();

        renderComponentParams();

        $.ajax({
            url: sprint_editor.getBlockWebPath('component') + '/ajax.php',
            type: 'post',
            data: {show_components: 1},
            dataType: 'json',
            success: function (result) {
                result['component_name'] = data.component_name;
                result['site_template'] = data.site_template;
                $elFilter.html(
                    sprint_editor.renderTemplate('component-select', result)
                );
            }
        });

        $elFilter.on('change', '.j-select-component', function () {
            var selectedName = $(this).val();
            if (selectedName != data.component_name) {
                data.component_name = selectedName;
                data.component_template = '';
                data.component_params = {};
                renderComponentParams();
            }
        });

        $elFilter.on('change', '.j-select-site_template', function () {
            var selectedName = $(this).val();
            if (selectedName != data.site_template) {
                data.site_template = selectedName;
                renderComponentParams();
            }
        });

        function renderComponentParams() {
            if (!data.component_name) {
                $elParams.removeClass('bxcompprop-wrap').removeAttr('style').empty();
                return;
            }

            BX.onCustomEvent(pmanager, 'OnComponentParamsDisplay', [{
                name: data.component_name,
                template: data.component_template,
                currentValues: data.component_params,
                siteTemplate: data.site_template,
                container: $elParams.get(0),
                parent: false,
                relPath: '/',
                callback: function (params, container) {
                    BX.onCustomEvent(pmanager, 'OnComponentParamsResize', [
                        boxWidth,
                        400
                    ]);
                }
            }]);

        }
    };

    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
});