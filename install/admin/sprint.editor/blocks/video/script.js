sprint_editor.registerBlock('video', function ($, $el, data) {

    data = $.extend({
        url: '',
        width: '100%',
        height: '480'
    }, data);

    var areas = [
        {
            dataKey: 'preview',
            blockName: 'image',
            container: '.sp-area1-image'
        }
    ];

    this.getData = function () {
        return data;
    };

    this.getAreas = function () {
        return areas;
    };

    this.collectData = function () {
        data.url = $el.find('.sp-url').val();
        data.width = $el.find('.sp-width').val();
        data.height = $el.find('.sp-height').val();
        return data;
    };

    this.afterRender = function () {
        var $input = $el.children('.sp-url');

        var $note = $el.children('.sp-note');
        var $hidden = $note.children('.sp-hidden');
        var $handle = $note.children('.sp-handle');

        getVideo($input.val());

        $input.bindWithDelay('input', function () {
            getVideo($(this).val());
        }, 500);

        $handle.on('click', function () {
            $hidden.toggle()
        });


        $el.on('click', '.sp-area1-toggle', function () {
            if ($el.hasClass('sp-show')) {
                $el.find('.sp-area1-image').hide(250);
                $el.removeClass('sp-show');
            } else {
                $el.find('.sp-area1-image').show(250);
                $el.addClass('sp-show');
            }
        });


    };

    function getVideo(someUrl) {
        $.ajax({
            url: sprint_editor.getBlockWebPath('video') + '/ajax.php',
            type: 'post',
            data: {url: someUrl},
            dataType: 'json',
            success: function (result) {
                $el.find('.sp-preview').html(result.html);
            }
        });
    }

});
