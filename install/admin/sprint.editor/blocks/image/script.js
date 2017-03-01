sprint_editor.registerBlock('image', function($, $el, data) {

    data = $.extend({
        file: {},
        desc: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.desc = $el.find('.j-image_item-text').val();
        return data;
    };

    this.afterRender = function () {

        renderfiles();

        var btn = $el.find('.j-fileupload-btn');
        var btninput = btn.find('input[type=file]');
        var label = btn.find('span');
        var labeltext = label.text();

        btninput.fileupload({
            url: sprint_editor.getBlockWebPath('image') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                $.each(result.result.file, function(index,file){
                    data.file = file;
                });

                renderfiles();
            },
            progressall: function (e, result) {
                var progress = parseInt(result.loaded / result.total * 100, 10);

                label.text('Загрузка: ' + progress + '%');

                if (progress>=100){
                    label.text(labeltext);
                }
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');


        $el.on("mouseenter", '.j-image_item', function () {
            $(this).addClass('sp-image_item-active');
            $(this).find('.j-image_item_panel').show()
        });

        $el.on("mouseleave", '.j-image_item', function () {
            $(this).removeClass('sp-image_item-active');
            $(this).find('.j-image_item_panel').hide()
        });

        $el.on('click', '.j-image_item-delete', function(){
            data['file'] = {};
            data['desc'] = '';
            renderfiles();
        });
    };

    var renderfiles = function() {
        $el.find('.j-fileupload-result').html(
            sprint_editor.renderTemplate('image-image', data)
        );
    }


});