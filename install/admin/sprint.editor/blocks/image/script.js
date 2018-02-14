sprint_editor.registerBlock('image', function($, $el, data) {

    data = $.extend({
        file: {},
        desc: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.desc = $el.find('.sp-image_item-text').val();
        return data;
    };

    this.afterRender = function () {

        renderfiles();

        var $btn = $el.find('.sp-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('strong');
        var labeltext = $label.text();

        $btninput.fileupload({
            url: sprint_editor.getBlockWebPath('image') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {

                deletefiles();

                $.each(result.result.file, function(index,file){
                    data.file = file;
                });

                renderfiles();
            },
            progressall: function (e, result) {
                var progress = parseInt(result.loaded / result.total * 100, 10);

                $label.text('Загрузка: ' + progress + '%');

                if (progress>=100){
                    $label.text(labeltext);
                }
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');


        $el.on('click', '.sp-image_item-del', function(){
            deletefiles();

            data['file'] = {};
            data['desc'] = '';
            renderfiles();
        });
    };

    var renderfiles = function() {
        $el.find('.sp-result').html(
            sprint_editor.renderTemplate('image-image', data)
        );
    };

    var deletefiles = function(){
        $.ajax({
            url: sprint_editor.getBlockWebPath('image') + '/delete.php',
            type: 'post',
            data: {
                file: data.file
            }
        });
    };

    this.beforeDelete = function () {
        deletefiles();
    }


});