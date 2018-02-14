sprint_editor.registerBlock('gallery', function ($, $el, data) {
    data = $.extend({
        images: []
    }, data);

    var itemsCollection = {};

    $.each(data.images, function (index, item) {
        var uid = sprint_editor.makeUid('sp');
        itemsCollection[uid] = item;
    });


    this.getData = function () {
        return data;
    };

    this.collectData = function () {

        data.images = [];

        $el.find('.sp-item').each(function () {
            var uid = $(this).data('uid');
            if (uid && itemsCollection[uid]) {
                data.images.push(itemsCollection[uid]);
            }

        });

        return data;
    };

    this.afterRender = function () {
        $.each(itemsCollection, function (uid, item) {
            renderitem(uid);
        });

        var $btn = $el.find('.sp-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('strong');
        var labeltext = $label.text();

        $btninput.fileupload({
            url: sprint_editor.getBlockWebPath('gallery') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                $.each(result.result.file, function (index, file) {
                    var uid = sprint_editor.makeUid('sp');
                    itemsCollection[uid] = {
                        file: file,
                        desc: ''
                    };
                    renderitem(uid);

                });


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

        $el.on('click', '.sp-item-del', function () {
            var $image = $el.find('.sp-active');
            $image.remove();
            closeedit();
        });

        $el.on('click', '.sp-item', function () {
            $el.find('.sp-item').removeClass('sp-active');
            $(this).addClass('sp-active');
            var uid = $(this).data('uid');

            openedit(uid);
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
                        var uid = sprint_editor.makeUid('sp');

                        itemsCollection[uid] = {
                            file: result.image,
                            desc: ''
                        };
                        renderitem(uid);
                    }

                    $urltext.val('');
                }
            });
        }, 500);


        var removeIntent = false;
        $el.find('.sp-result').sortable({
            items: ".sp-item",
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
                    closeedit();
                } else {
                    ui.item.removeAttr('style');
                }

            }
        });
    };

    var renderitem = function (uid) {
        if (!itemsCollection[uid]) {
            return;
        }

        var item = itemsCollection[uid];
        var $item = $el.find('[data-uid="' + uid + '"]');

        if ($item.length > 0) {
            $item.replaceWith(sprint_editor.renderTemplate('gallery-images', {
                item: item,
                uid: uid,
                active:1
            }));

        } else {
            $el.find('.sp-result').append(sprint_editor.renderTemplate('gallery-images', {
                item: item,
                uid: uid,
                active:0
            }));
        }
    };

    var closeedit = function () {
        $el.find('.sp-edit').hide(250).empty();
    };

    var openedit = function (uid) {
        if (!itemsCollection[uid]) {
            return;
        }
        $el.find('.sp-edit').html(
            sprint_editor.renderTemplate('gallery-edit', itemsCollection[uid])
        ).show(250);

        $el.find('.sp-item-desc').bindWithDelay('input', function () {
            itemsCollection[uid].desc = $(this).val();
        }, 500);


    }
});