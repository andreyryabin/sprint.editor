sprint_editor.registerBlock('image', function ($, $el, data) {

    data = $.extend({
        file: {},
        desc: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.desc = $el.find('.sp-item-text').val();
        return data;
    };

    this.afterRender = function () {

        renderfiles();

        var $btn = $el.find('.sp-x-btn-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('label');
        var labeltext = $label.text();

        $btninput.fileupload({
            dropZone: $el,
            url: sprint_editor.getBlockWebPath('image') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                let found = false;
                $.each(result.result.file, function (index, file) {
                    data.file = file;
                    found = true;
                });

                if (found) {
                    renderfiles();
                    togglepanel(false);
                }

            },
            progressall: function (e, result) {
                var progress = parseInt(result.loaded / result.total * 100, 10);

                $label.text('Загрузка: ' + progress + '%');

                if (progress >= 100) {
                    $label.text(labeltext);
                }
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');


        $el.find('.sp-download-url').bindWithDelay('input', function () {
            var $urltext = $(this);

            var urlvalue = $.trim(
                $urltext.val()
            );

            if (urlvalue.length <= 0) {
                return false;
            }


            $.ajax({
                url: sprint_editor.getBlockWebPath('image') + '/download.php',
                type: 'post',
                data: {
                    url: urlvalue
                },
                dataType: 'json',
                success: function (result) {
                    if (result.image) {
                        data.file = result.image;

                        renderfiles();

                        togglepanel(false);
                    }

                    $urltext.val('');


                }
            });
        }, 500);

        $el.on('click', '.sp-item-del', function () {
            data.file = {};

            renderfiles();

            togglepanel(true);
        });

        if (!data.file || !data.file.SRC) {
            togglepanel(true);
        } else {
            togglepanel(false);
        }

    };

    var togglepanel = function (show) {
        if (show) {
            $el.addClass('sp-show');
        } else {
            $el.removeClass('sp-show');
        }
    }

    var renderfiles = function () {
        $el.find('.sp-result').html(
            sprint_editor.renderTemplate('image-image', data)
        );
    };

});
