sprint_editor.registerBlock('gallery', function ($, $el, data) {
    data = $.extend({
        images: []

    }, data);

    var hideSource = 1;
    /*if (data.images.length <=0 ){
        hideSource = 0;
    }*/

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.afterRender = function () {
        renderSource();
        renderfiles();

        var btn = $el.find('.sp-file');
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

        $el.on('click', '.sp-image_item-del', function () {
            var index = $el.find('.sp-image_item-del').index(this);
            var item = $(this).closest('.sp-image_item');

            if (data.images[index]) {
                data.images.splice(index, 1);
                item.remove();
            }
        });

        $el.on('click', '.sp-toggle-source', function () {
            toggleSource();
        });

        var $urltext = $el.find('.sp-download-url');
        var $urlsubmit = $el.find('.sp-download-btn');

        $urlsubmit.on('click', function () {
            submitImageUrl();
        });

        function submitImageUrl() {
            var buttonText = $urlsubmit.val();

            var urlvalue = $urltext.val();
            urlvalue = $.trim(urlvalue);

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

    var renderSource = function(){
        var $obj = $el.find('.sp-source');
        if (hideSource){
            $obj.hide();
        } else {
            $obj.show();
        }
    };

    var toggleSource = function(){
        if (hideSource){
            hideSource = 0;
            renderSource();

        } else {
            hideSource = 1;
            renderSource();
        }
    };

    var renderfiles = function () {
        $el.find('.sp-result').html(
            sprint_editor.renderTemplate('gallery-images', data)
        );
        $el.find('.sp-image_item-text').each(function () {
            var index = $el.find('.sp-image_item-text').index(this);

            $(this).bindWithDelay('input', function () {
                if (data.images[index]) {
                    data.images[index].desc = $(this).val();
                }
            }, 500);

            //$(this).mouseenter( handlerIn ).mouseleave( handlerOut );

        });


    }

});