sprint_editor.registerBlock('gallery', function ($, $el, data) {
    data = $.extend({
        images: []
    }, data);

    var imageCollection = {};

    $.each(data.images, function (index, item) {
        var uid = sprint_editor.makeUid('spg');
        imageCollection[uid] = item;
    });



    this.getData = function () {
        return data;
    };

    this.collectData = function () {

        data.images = [];

        $el.find('.sp-image').each(function () {
            var uid = $(this).data('uid');
            if (uid && imageCollection[uid]) {
                data.images.push(imageCollection[uid]);
            }

        });

        return data;
    };

    this.afterRender = function () {

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
                    var uid = sprint_editor.makeUid('spg');
                    imageCollection[uid] = {
                        file: file,
                        desc: ''
                    };
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


        $el.on('click', '.sp-toggle', function () {
            if ($el.hasClass('sp-show')) {
                $el.find('.sp-source').hide(250);
                $el.removeClass('sp-show');
            } else {
                $el.find('.sp-source').show(250);
                $el.addClass('sp-show');
            }
        });

        $el.on('click', '.sp-image-del', function () {

            var $image = $el.find('.sp-active');

            $image.remove();
            $el.find('.sp-edit').hide(250).empty();
        });

        $el.on('click', '.sp-image', function () {

            $el.find('.sp-image').removeClass('sp-active');

            $(this).addClass('sp-active');

            var $image = $el.find('.sp-active');
            var uid = $image.data('uid');

            if (imageCollection[uid]) {
                $el.find('.sp-edit').html(
                    sprint_editor.renderTemplate('gallery-edit', imageCollection[uid])
                ).show(250);

                $el.find('.sp-image-desc').bindWithDelay('input', function () {
                    imageCollection[uid].desc = $(this).val();
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
                        var uid = sprint_editor.makeUid('spg');

                        imageCollection[uid] = {
                            file: result.image,
                            desc: ''
                        };
                        renderfiles();
                    }

                    $urltext.val('');
                }
            });
        }, 500);


        var removeIntent = false;
        $el.find('.sp-result').sortable({
            items: ".sp-image",
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

            }
        });
    };

    var renderfiles = function () {
        $el.find('.sp-edit').hide(250).empty();

        $el.find('.sp-result').html(
            sprint_editor.renderTemplate('gallery-images', {
                images: imageCollection
            })
        );


    }

});