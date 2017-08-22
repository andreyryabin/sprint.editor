sprint_editor.registerBlock('video_gallery', function ($, $el, data) {
    data = $.extend({
        items: []

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
        var $label = $btn.find('span');
        var labeltext = $label.text();

        $btninput.fileupload({
            url: sprint_editor.getBlockWebPath('video_gallery') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                $.each(result.result.file, function (index, file) {
                    data.items.push({
                        file: file,
                        video:'',
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

        $el.on('click', '.sp-item-del', function () {

            var $items = $el.find('.sp-item').filter('.active');
            var index = $el.find('.sp-item').index($items);

            if (data.items[index]) {
                data.items.splice(index, 1);
                $items.remove();
                $el.find('.sp-edit').hide(250).empty();
            }

        });

        $el.on('click','.sp-item',function(){

            $el.find('.sp-item').removeClass('active');

            $(this).addClass('active');


            var $items = $el.find('.sp-item').filter('.active');
            var index = $el.find('.sp-item').index($items);

            if (data.items[index]){
                $el.find('.sp-edit').html(
                    sprint_editor.renderTemplate('video_gallery-edit', data.items[index])
                ).show(250);

                $el.find('.sp-item-desc').bindWithDelay('input', function () {
                    data.items[index].desc = $(this).val();
                }, 500);

                $el.find('.sp-item-video').bindWithDelay('input', function () {
                    var val = $.trim($(this).val());

                    if (val){
                        $items.addClass('sp-is-video');
                    } else {
                        $items.removeClass('sp-is-video');
                    }

                    data.items[index].video = val;
                }, 500);
            }


        });


        $el.on('click', '.sp-download-btn', function () {
            var $urlsubmit = $(this);
            var $urltext = $el.find('.sp-download-url');

            var buttonText = $urlsubmit.val();

            var urlvalue = $.trim(
                $urltext.val()
            );

            if (urlvalue.length <= 0) {
                return false;
            }

            $urlsubmit.val('...');

            $.ajax({
                url: sprint_editor.getBlockWebPath('video_gallery') + '/download.php',
                type: 'post',
                data: {
                    url: urlvalue
                },
                dataType: 'json',
                success: function (result) {
                    if (result.items) {
                        data.items.push({
                            file: result.items,
                            video: result.video,
                            desc: ''
                        });
                        renderfiles();
                    }

                    $urltext.val('');
                    $urlsubmit.val(buttonText);
                }
            });
        });
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
            sprint_editor.renderTemplate('video_gallery-items', data)
        );
    }

});