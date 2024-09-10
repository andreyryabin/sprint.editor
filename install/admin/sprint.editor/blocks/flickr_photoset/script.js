sprint_editor.registerBlock('flickr_photoset', function ($, $el, data, settings) {

    var navparams = {
        count_pages: 1,
        current_page: 1,
    };

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
        loadPhotosetById(data.photoset_id, true);

        $el.find('.sp-photoset-id').bindWithDelay('input', function () {
            loadPhotosetById($(this).val());
        }, 500);

        $el.find('.sp-toggle').on('click', function () {
            loadPhotosets(navparams.current_page, true);
        });

        $el.on('click', '.sp-photoset-item', function () {
            let id = $(this).data('id');

            $el.find('.sp-photoset-id').val(id);

            loadPhotosetById(id);
        })

        $el.on('click', '.sp-nav-left', function () {
            if (navparams.current_page - 1 >= 1) {
                loadPhotosets(navparams.current_page - 1);
            }
        });

        $el.on('click', '.sp-nav-right', function () {
            if (navparams.current_page + 1 <= navparams.count_pages) {
                loadPhotosets(navparams.current_page + 1);
            }
        });
    };

    var loadPhotosets = function (page, showLoading) {
        navparams.current_page = page;

        $el.find('.sp-photosets').show();
        // $el.find('.sp-source').hide();

        let $response = $el.find('.sp-photosets');

        if (showLoading) {
            $response.text('Загрузка ...');
        }

        $.ajax({
            url: sprint_editor.getBlockWebPath('flickr_photoset') + '/ajax.php',
            type: 'post',
            data: {page: page},
            dataType: 'json',
            success: function (result) {
                if (result.stat && result.stat === 'ok' && result.photosets && result.photosets.photoset) {
                    $response.html(
                        sprint_editor.renderTemplate('flickr_photoset-photosets', result)
                    );

                    navparams.count_pages = result.photosets.pages;

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

    var loadPhotosetById = function (photoset_id, showLoading) {
        let $response = $el.find('.sp-info');

        if (!photoset_id) {
            $response.text('Альбом не выбран');
            return;
        }

        $el.find('.sp-photosets').hide();
        // $el.find('.sp-source').show();

        $response.css({'opacity': 0.5});

        if (showLoading) {
            $response.text('Загрузка ...');
        }

        $.ajax({
            url: sprint_editor.getBlockWebPath('flickr_photoset') + '/ajax.php',
            type: 'post',
            data: {photoset_id: photoset_id},
            dataType: 'json',
            success: function (result) {
                if (result.stat && result.stat === 'ok' && result.photoset) {
                    $response.html(
                        sprint_editor.renderTemplate('flickr_photoset-info', result)
                    );
                } else if (result.stat && result.stat === 'fail') {
                    $response.text(result.message);
                } else {
                    $response.text('Альбом не найден');
                }

                $response.css({'opacity': 1});
            },
            error: function (xhr, status, error) {
                $response.text('Ошибка соединения с flickr');
                $response.css({'opacity': 1});
            },
        });
    }

});
