sprint_editor.registerBlock('flickr_photoset', function ($, $el, data, settings) {

    data = $.extend({
        photoset_id: '',
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.photoset_id = $el.find('.sp-photoset-id').val();
        return data;
    };

    this.afterRender = function () {
        loadPhotosetById(data.photoset_id);

        $el.find('.sp-photoset-id').bindWithDelay('input', function () {
            loadPhotosetById($(this).val());
        }, 500);
    };

    var loadPhotosetById = function (photoset_id) {
        let $response = $el.find('.sp-result');

        if (!photoset_id) {
            $response.text('Укажите ID альбома');
            return;
        }

        $response.text('Загрузка ...');

        $.ajax({
            url: sprint_editor.getBlockWebPath('flickr_photoset') + '/ajax.php',
            type: 'post',
            data: {photoset_id: photoset_id},
            dataType: 'json',
            success: function (result) {
                if (result.stat && result.stat === 'ok' && result.photoset) {
                    $response.html(
                        'Альбом: ' + result.photoset.title._content + '<br/>' +
                        'Фотографий: ' + result.photoset.photos
                    );
                } else if (result.stat && result.stat === 'fail') {
                    $response.text(result.message);
                } else {
                    $response.text('Альбом не найден');
                }
            },
            error: function (xhr, status, error) {
                $response.text('Ошибка соединения с flickr');
            },
        });
    }

});
