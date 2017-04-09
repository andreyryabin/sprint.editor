sprint_editor.registerBlock('component', function ($, $el, data) {

    data = $.extend({
        component_name: '',
        component_template: '',
        component_params: {}
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

        var $select = $el.find('.j-select');
        var boxWidth = $el.width();
        var pParamsContainer = $el.find('.j-result').get(0);

        renderComponentParams();

        $.ajax({
            url: sprint_editor.getBlockWebPath('component') + '/ajax.php',
            type: 'post',
            data: {},
            dataType: 'json',
            success: function (result) {
                result['component_name'] = data.component_name;
                $select.html(
                    sprint_editor.renderTemplate('component-select', result)
                );
            },
            error: function () {

            }
        });


        $select.on('change', 'select', function () {
            var selectedName = $(this).val();
            if (selectedName != data.component_name){
                data.component_name = selectedName;
                data.component_template = '';
                data.component_params = {};
                renderComponentParams();
            }
        });


        function renderComponentParams(){
            if (!data.component_name){
                $(pParamsContainer).empty();
            } else {
                BX.onCustomEvent(pmanager, 'OnComponentParamsDisplay', [{
                    name: data.component_name,
                    template: data.component_template,
                    currentValues: data.component_params,
                    parent: false,
                    container: pParamsContainer,
                    siteTemplate: '',
                    relPath: '/',
                    callback: function (params, container) {
                        BX.onCustomEvent(pmanager, 'OnComponentParamsResize', [
                            boxWidth,
                            400
                        ]);
                    }
                }]);
            }
        }
    };

    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
});