sprint_editor.registerBlock('my_tree_files', function ($, $el, data) {
    function findChildren(item){
        var ttt = [];
        item.find('> .sp-item').each(function( index, value ) {
            var uid = $(this).data('uid');
            if (uid && itemsCollection[uid]) {
                ttt[index] = itemsCollection[uid]
            }

            var children = findChildren($(this).find('> ul'));
            //console.log(children);
            if(children.length != 0){
                ttt[index].children = children;
            }
        });
        return ttt;
    }

    data = $.extend({
        files: []
    }, data);

    var itemsCollection = {};
    var globalUid = false;

    $.each(data.files, function (index, item) {
        var uid = sprint_editor.makeUid();
        itemsCollection[uid] = item;
    });


    this.getData = function () {
        return data;
    };

    this.collectData = function () {

        data.files = [];

/*         $el.find('.sp-item').each(function () {
            var uid = $(this).data('uid');
            if (uid && itemsCollection[uid]) {
                data.files.push(itemsCollection[uid]);
            }
        }); */
        data.files = findChildren($el.find('.sortableLists'))
        return data;
    };

    this.afterRender = function () {
        console.log(data);
        $.each(itemsCollection, function (uid, item) {
            renderitem(uid);
        });

        var $btn = $el.find('.sp-file');
        var $btninput = $btn.find('input[type=file]');
        var $label = $btn.find('strong');
        var labeltext = $label.text();

        $el.find('.sp-item-desc').bindWithDelay('input', function () {
            if (globalUid && itemsCollection[globalUid]) {
                itemsCollection[globalUid].desc = $(this).val();
            }
        }, 500);

        $btninput.fileupload({
            dropZone: $el,
            url: sprint_editor.getBlockWebPath('my_files') + '/upload.php',
            dataType: 'json',
            done: function (e, result) {
                $.each(result.result.file, function (index, file) {
                    var uid = sprint_editor.makeUid();
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
            $el.find('.sp-active').remove();
            closeedit();
        });

        $el.on('click', '.sp-item', function () {
            $el.find('.sp-item').removeClass('sp-active');
            $(this).addClass('sp-active');
            openedit($(this).data('uid'));
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
                url: sprint_editor.getBlockWebPath('my_files') + '/download.php',
                type: 'post',
                data: {
                    url: urlvalue
                },
                dataType: 'json',
                success: function (result) {
                    if (result.file) {
                        var uid = sprint_editor.makeUid();

                        itemsCollection[uid] = {
                            file: result.file,
                            desc: ''
                        };
                        renderitem(uid);
                    }

                    $urltext.val('');
                }
            });
        }, 500);


        var removeIntent = false;
        $el.find('.sortableLists').sortableLists({
            // Like a css class name. Class will be removed after drop.
            currElClass: 'currElemClass',
            // or like a jQuery css object. Note that css object settings can't be removed
            currElCss: {'background-color':'green', 'color':'#fff'},
            	// Like a css class name. Class will be removed after drop.
            currElClass: 'currElemClass',
            // or like a jQuery css object. Note that css object settings can't be removed
            currElCss: {'background-color':'green', 'color':'#fff'},
            hintClass: 'hintClass',
            // or like a jQuery css object
            hintCss: {'background-color':'green', 'border':'1px dashed white'}
        });
    };

    var renderitem = function (uid) {
        if (!itemsCollection[uid]) {
            return;
        }

        var item = itemsCollection[uid];
        var $item = $el.find('[data-uid="' + uid + '"]');

        if ($item.length > 0) {
            $item.replaceWith(sprint_editor.renderTemplate('my_tree_files-item', {
                item: item,
                uid: uid,
                active: 1
            }));

        } else {
            $el.find('.sp-result').append(sprint_editor.renderTemplate('my_tree_files-item', {
                item: item,
                uid: uid,
                active: 0
            }));
        }
    };

    var closeedit = function () {
        globalUid = false;
        $el.find('.sp-edit').hide(250);
        $el.find('.sp-item-desc').val('');
    };

    var openedit = function (uid) {
        if (itemsCollection[uid]) {
            globalUid = uid;
            $el.find('.sp-item-desc').val(itemsCollection[uid].desc);
            $el.find('.sp-edit').show(250);
        }
    };



/*     this.getData = function () {
        return data;
    };
    this.collectData = function () {
        return data;
    };

    this.afterRender = function () {
        $el.find('.sortableLists').sortableLists({
            // Like a css class name. Class will be removed after drop.
            currElClass: 'currElemClass',
            // or like a jQuery css object. Note that css object settings can't be removed
            currElCss: {'background-color':'green', 'color':'#fff'},
            	// Like a css class name. Class will be removed after drop.
            currElClass: 'currElemClass',
            // or like a jQuery css object. Note that css object settings can't be removed
            currElCss: {'background-color':'green', 'color':'#fff'},
            hintClass: 'hintClass',
            // or like a jQuery css object
            hintCss: {'background-color':'green', 'border':'1px dashed white'}
        });




    } */
});
