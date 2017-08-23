sprint_editor.registerBlock('video_gallery', function ($, $el, data) {
    data = $.extend({
        items: []
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.afterRender = function () {

        $.each(data.items, function (index, item) {
            $el.find('.sp-result').append(
                sprint_editor.renderTemplate('video_gallery-item', item)
            );
        });

        $el.on('click', '.sp-item-add', function () {
            var item = {
                video: '',
                file: '',
                desc: ''
            };

            data.items.push(item);

            $el.find('.sp-result').append(
                sprint_editor.renderTemplate('video_gallery-item', item)
            );

        });

        $el.on('click', '.sp-item', function () {
            $el.find('.sp-item').removeClass('sp-active');

            var index = $el.find('.sp-item').index($(this));
            if (data.items[index]) {
                renderEditForm(index);
            } else {
                $el.find('.sp-edit').hide(250).empty();
            }
        });

    };

    function renderEditForm(index) {

        $el.find('.sp-item').eq(index).addClass('sp-active');

        $el.find('.sp-edit').html(
            sprint_editor.renderTemplate('video_gallery-edit', data.items[index])
        ).show(250);

        $el.find('.sp-item-del').on('click', function () {
            data.items.splice(index, 1);
            $el.find('.sp-item').eq(index).remove();
            $el.find('.sp-edit').hide(250).empty();
        });

        $el.find('.sp-item-desc').bindWithDelay('input', function () {
            data.items[index].desc = $(this).val();
        }, 500);

        $el.find('.sp-item-video').bindWithDelay('input', function () {

            var val = $(this).val();

            if (val && !data.items[index].file && !data.items[index].file.ID) {
                $.ajax({
                    url: sprint_editor.getBlockWebPath('video_gallery') + '/download.php',
                    type: 'post',
                    data: {
                        url: val
                    },
                    dataType: 'json',
                    success: function (result) {
                        if (result.image) {
                            data.items[index].file = result.image;
                            data.items[index].video = val;
                        }

                        $el.find('.sp-item').eq(index).replaceWith(
                            sprint_editor.renderTemplate('video_gallery-item', data.items[index])
                        );

                        $el.find('.sp-item').eq(index).addClass('sp-active');

                    }
                });
            } else {
                data.items[index].video = val;

                $el.find('.sp-item').eq(index).replaceWith(
                    sprint_editor.renderTemplate('video_gallery-item', data.items[index])
                );

                $el.find('.sp-item').eq(index).addClass('sp-active');

            }


        }, 500);


        var $btn = $el.find('.sp-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('strong');
        var labeltext = $label.text();

        $btninput.fileupload({
            url: sprint_editor.getBlockWebPath('video_gallery') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                data.items[index].file = result.result.file[0];

                $el.find('.sp-item').eq(index).replaceWith(
                    sprint_editor.renderTemplate('video_gallery-item', data.items[index])
                );

                $el.find('.sp-item').eq(index).addClass('sp-active');
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
    }

});