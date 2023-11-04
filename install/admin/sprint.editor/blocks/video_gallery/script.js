sprint_editor.registerBlock('video_gallery', function ($, $el, data) {
    data = $.extend({
        items: []
    }, data);

    var itemsCollection = {};

    $.each(data.items, function (index, item) {
        var uid = sprint_editor.makeUid();
        itemsCollection[uid] = item;
    });

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.items = [];

        $el.find('.sp-item').each(function () {
            var uid = $(this).data('uid');
            if (uid && itemsCollection[uid]) {
                data.items.push(itemsCollection[uid]);
            }
        });

        return data;
    };

    this.afterRender = function () {

        $.each(itemsCollection, function (uid, item) {
            renderitem(uid);
        });


        $el.on('click', '.sp-item-del', function () {
            removeedit($el.find('.sp-x-active'));
        });

        $el.on('click', '.sp-item-add', function () {
            var uid = sprint_editor.makeUid();

            itemsCollection[uid] = {
                video: '',
                file: '',
                desc: ''
            };

            renderitem(uid);

        });

        $el.on('click', '.sp-item', function () {
            openedit($(this));
        });


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
                    removeedit(ui.item);
                } else {
                    ui.item.removeAttr('style');
                }

            }
        });

    };

    var openedit = function ($item) {

        if ($item.hasClass('sp-x-active')) {
            $item.removeClass('sp-x-active')
            $el.find('.sp-edit').hide();
            return;
        }

        $el.find('.sp-item').removeClass('sp-x-active');

        $item.addClass('sp-x-active');
        var uid = $item.data('uid');

        if (!itemsCollection[uid]) {
            return;
        }

        $el.find('.sp-edit').html(
            sprint_editor.renderTemplate('video_gallery-edit', itemsCollection[uid])
        ).show(250);

        var $btn = $el.find('.sp-x-btn-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('label');
        var labeltext = $label.text();


        $el.find('.sp-item-desc').bindWithDelay('input', function () {
            itemsCollection[uid].desc = $(this).val();
        }, 500);

        $el.find('.sp-item-video').bindWithDelay('input', function () {
            var val = $(this).val();

            if (val && !itemsCollection[uid].file && !itemsCollection[uid].file.ID) {
                $.ajax({
                    url: sprint_editor.getBlockWebPath('video_gallery') + '/download.php',
                    type: 'post',
                    data: {
                        url: val
                    },
                    dataType: 'json',
                    success: function (result) {
                        itemsCollection[uid].file = result.image;
                        itemsCollection[uid].video = val;
                        renderitem(uid);
                    }
                });
            } else {
                itemsCollection[uid].video = val;
                renderitem(uid);
            }


        }, 500);


        $btninput.fileupload({
            dropZone: $el,
            url: sprint_editor.getBlockWebPath('video_gallery') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                itemsCollection[uid].file = result.result.file[0];

                renderitem(uid);
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
    };


    var removeedit = function ($image) {
        var $prev = $image.prev('.sp-item');

        $image.remove();

        if ($prev.length > 0) {
            openedit($prev);
            return;
        }

        var $last = $el.find('.sp-item').last();
        if ($last.length > 0) {
            openedit($last);
            return;
        }

        $el.find('.sp-edit').hide();
    };

    var renderitem = function (uid) {
        if (!itemsCollection[uid]) {
            return;
        }

        var item = itemsCollection[uid];
        var $item = $el.find('[data-uid="' + uid + '"]');

        if ($item.length > 0) {
            $item.replaceWith(sprint_editor.renderTemplate('video_gallery-item', {
                item: item,
                uid: uid,
                active: 1
            }));

        } else {
            $el.find('.sp-result').append(sprint_editor.renderTemplate('video_gallery-item', {
                item: item,
                uid: uid,
                active: 0
            }));
        }
    };

});
