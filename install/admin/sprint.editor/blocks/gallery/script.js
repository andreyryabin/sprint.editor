sprint_editor.registerBlock('gallery', function ($, $el, data) {
    data = $.extend({
        images: []

    }, data);

    var hideSource = 1;

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.afterRender = function () {
        renderSource();
        renderfiles();

        var $btn = $el.find('.sp-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('strong');
        var labeltext = $label.text();

        $btninput.fileupload({
            url: sprint_editor.getBlockWebPath('gallery') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                $.each(result.result.file, function (index, file) {
                    data.images.push({
                        file: file,
                        desc: ''
                    });
                });

                renderfiles();
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


        $el.on('click', '.sp-toggle-source', function () {
            toggleSource();
        });

        $el.on('click', '.sp-image-del', function () {

            var $image = $el.find('.sp-active');
            var index = $el.find('.sp-image').index($image);

            if (data.images[index]) {
                data.images.splice(index, 1);
                $image.remove();
                $el.find('.sp-edit').hide(250).empty();
            }

        });

        $el.on('click','.sp-image',function(){

            $el.find('.sp-image').removeClass('sp-active');

            $(this).addClass('sp-active');


            var $image = $el.find('.sp-active');
            var index = $el.find('.sp-image').index($image);

            if (data.images[index]){
                $el.find('.sp-edit').html(
                    sprint_editor.renderTemplate('gallery-edit', data.images[index])
                ).show(250);

                $el.find('.sp-image-desc').bindWithDelay('input', function () {
                    data.images[index].desc = $(this).val();
                }, 500);

            }


        });


        $el.find('.sp-download-url').bindWithDelay('input', function () {
            var $urltext = $(this);

            var urlvalue = $.trim(
                $urltext.val()
            );

            if (urlvalue.length <= 0) {
                return false;
            }


            $.ajax({
                url: sprint_editor.getBlockWebPath('gallery') + '/download.php',
                type: 'post',
                data: {
                    url: urlvalue
                },
                dataType: 'json',
                success: function (result) {
                    if (result.image) {
                        data.images.push({
                            file: result.image,
                            desc: ''
                        });
                        renderfiles();
                    }

                    $urltext.val('');
                }
            });
        }, 500);


    };

    var renderSource = function () {
        var $obj = $el.find('.sp-source');
        if (hideSource) {
            $obj.hide(250);
        } else {
            $obj.show(250);
        }
    };

    var toggleSource = function () {
        if (hideSource) {
            hideSource = 0;
            renderSource();

        } else {
            hideSource = 1;
            renderSource();
        }
    };

    var renderfiles = function () {
        $el.find('.sp-edit').hide(250).empty();

        $el.find('.sp-result').html(
            sprint_editor.renderTemplate('gallery-images', data)
        );
    }

});