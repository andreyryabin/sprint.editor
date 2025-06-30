sprint_editor.registerBlock('files', function ($, $el, data, settings) {
    data = $.extend({
        files: []
    }, data);

    var itemsCollection = {};
    var globalUid = false;

    $.each(data.files, function (index, item) {
        var uid = sprint_editor.makeUid();
        itemsCollection[uid] = item;
    });

    var multiple = true;
    if (settings.hasOwnProperty('multiple') && settings.multiple.hasOwnProperty('value')) {
        multiple = !!settings.multiple.value;
    }


    this.getData = function () {
        data['multiple'] = multiple;

        return data;
    };

    this.collectData = function () {
        data.files = [];

        $el.find('.sp-item').each(function () {
            var uid = $(this).data('uid');
            if (uid && itemsCollection[uid]) {
                data.files.push(itemsCollection[uid]);
            }

        });

        delete data['multiple'];
        return data;
    };

    this.afterRender = function () {
        rendercollection();

        var $btn = $el.find('.sp-x-btn-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('label');
        var labeltext = $label.text();

        $el.find('.sp-item-desc').bindWithDelay('input', function () {
            if (globalUid && itemsCollection[globalUid]) {
                let fileDescription = $(this).val();
                itemsCollection[globalUid].desc = fileDescription;
                if (itemsCollection[globalUid].file && itemsCollection[globalUid].file.ID) {
                    itemsCollection[globalUid].file.DESCRIPTION = fileDescription;
                    $.ajax({
                        url: sprint_editor.getBlockWebPath('files') + '/desc.php',
                        type: 'post',
                        data: itemsCollection[globalUid].file,
                    });
                }
            }
        }, 500);

        $btninput.fileupload({
            dropZone: $el,
            url: sprint_editor.getBlockWebPath('files') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                if (!data.multiple) {
                    globalUid = false;
                    itemsCollection = {};
                }

                $.each(result.result.file, function (index, file) {
                    var uid = sprint_editor.makeUid();
                    itemsCollection[uid] = {file: file, desc: ''};
                });

                rendercollection();

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


        $el.on('click', '.sp-item-del', function () {
            $el.find('.sp-x-active').remove();
            closeedit();
        });

        $el.on('click', '.sp-item', function (e) {
            e.stopPropagation();
            if ($(this).hasClass('sp-x-active')) {
                $(this).removeClass('sp-x-active');
                closeedit();
            } else {
                $el.find('.sp-item').removeClass('sp-x-active');
                $(this).addClass('sp-x-active');
                openedit($(this).data('uid'));
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
                url: sprint_editor.getBlockWebPath('files') + '/download.php',
                type: 'post',
                data: {
                    url: urlvalue
                },
                dataType: 'json',
                success: function (result) {
                    if (result.file) {
                        if (!data.multiple) {
                            globalUid = false;
                            itemsCollection = {};
                        }

                        let uid = sprint_editor.makeUid();
                        itemsCollection[uid] = {file: result.file, desc: ''};

                        rendercollection();
                        $urltext.val('');
                    }
                }
            });
        }, 500);


        $el.find('.sp-result').sortable({
            items: ".sp-item",
            placeholder: "sp-placeholder",
        });

        if (!data.files || !data.files.length) {
            closeedit();
        }
    };

    var rendercollection = function () {
        let $res = $el.find('.sp-result');

        if (!data.multiple) {
            $res.empty();
            closeedit();
        }

        $.each(itemsCollection, function (uid, item) {
            let $item = $res.find('[data-uid="' + uid + '"]');
            if (!$item.length) {
                $res.append(sprint_editor.renderTemplate('files-item', {
                    item: item,
                    uid: uid,
                    active: 0
                }));
            }
        });

    }

    var closeedit = function () {
        globalUid = false;
        $el.find('.sp-item-desc').val('');
        $el.removeClass('sp-editable');
    };

    var openedit = function (uid) {
        if (itemsCollection[uid]) {
            globalUid = uid;
            $el.find('.sp-item-desc').val(itemsCollection[uid].desc);
            $el.addClass('sp-editable');
        }
    };

});
