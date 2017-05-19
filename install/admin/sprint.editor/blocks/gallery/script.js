sprint_editor.registerBlock('gallery', function ($, $el, data) {
    data = $.extend({
        images: []

    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.afterRender = function () {
        renderfiles();

        var btn = $el.find('.j-fileupload-btn');
        var btninput = btn.find('input[type=file]');
        var label = btn.find('span');
        var labeltext = label.text();

        btninput.fileupload({
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
                label.text('Загрузка: ' + progress + '%');
                if (progress >= 100) {
                    label.text(labeltext);
                }
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');

        $el.on('click', '.j-image_item-delete', function () {
            var index = $el.find('.j-image_item-delete').index(this);
            var item = $(this).closest('.j-image_item');

            if (data.images[index]) {
                data.images.splice(index, 1);
                item.remove();
            }
        });

        $el.on('click', '.j-url-toggle', function () {
            var $obj = $el.find('.j-url-tab');
            if ($obj.is(':hidden')) {
                $(this).addClass('active');
                $obj.show();
            } else {
                $(this).removeClass('active');
                $obj.hide();
            }
        });

        $el.on("mouseenter", '.j-image_item', function () {
            $(this).addClass('sp-image_item-active');
            $(this).find('.j-image_item_panel').show()
        });

        $el.on("mouseleave", '.j-image_item', function () {
            $(this).removeClass('sp-image_item-active');
            $(this).find('.j-image_item_panel').hide()
        });

        var $urltext = $el.find('.j-url-tab input[type=text]');
        var $urlsubmit = $el.find('.j-url-tab input[type=button]');

        $urlsubmit.on('click', function () {
            submitImageUrl();
        });

        function submitImageUrl() {
            var buttonText = $urlsubmit.val();

            var urlvalue = $urltext.val();

            if (urlvalue.length <= 0) {
                return false;
            }

            $urlsubmit.val('...');

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
                    $urlsubmit.val(buttonText);
                }
            });
        }
    };

    var renderfiles = function () {
        $el.find('.j-fileupload-result').html(
            sprint_editor.renderTemplate('gallery-images', data)
        );
        $el.find('.j-image_item-text').each(function () {
            var index = $el.find('.j-image_item-text').index(this);

            $(this).bindWithDelay('input', function () {
                if (data.images[index]) {
                    data.images[index].desc = $(this).val();
                }
            }, 500);

            //$(this).mouseenter( handlerIn ).mouseleave( handlerOut );

        });


    }

});