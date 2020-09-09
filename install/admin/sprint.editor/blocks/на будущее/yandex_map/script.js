sprint_editor.registerBlock('yandex_map', function ($, $el, data) {

    var myPlacemark = null;
    var myMap = null;

    data = $.extend({
        placemarks: [],
        zoom: 12,
        center: [55.76, 37.64]
    }, data);

    this.getData = function () {
        data['map_id'] = 'map' + Math.random().toString(36).substring(7);
        return data;
    };

    this.collectData = function () {
        delete data['map_id'];

        if (!myMap) {
            return data;
        }

        data.zoom = myMap.getZoom();
        data.placemarks = [];

        myMap.geoObjects.each(function (placeMark) {
            var coords = placeMark.geometry.getCoordinates();
            var text = getBalloonContent(placeMark);

            if (coords) {
                data.placemarks.push({
                    coords: coords,
                    text: text
                });
            }
        });

        if (myPlacemark) {
            data.center = myPlacemark.geometry.getCoordinates();
        }

        return data;
    };

    this.afterRender = function () {

        $.getScript("https://api-maps.yandex.ru/2.1/?lang=ru_RU&wizard=bitrix", function () {
            afterLoad()
        });
    };

    function afterLoad() {
        if (!window.ymaps) {
            return false;
        }
        var defaultCenter = data.center;
        var defaultZoom = data.zoom;

        ymaps.ready(function () {
            myMap = new ymaps.Map(data['map_id'], {
                center: defaultCenter,
                zoom: defaultZoom,
                behaviors: ['default'],
                controls: ['geolocationControl', 'searchControl', 'zoomControl']
            }, {
                balloonPanelMaxMapArea: Infinity
                // balloonAutoPan: false
                // balloonCloseButton : false
            });

            myMap.behaviors.disable('scrollZoom');
            myMap.behaviors.disable('dblClickZoom');

            var searchControl = myMap.controls.get('searchControl');
            searchControl.options.set({
                noPlacemark: true,
                maxWidth: "small",
                noSuggestPanel: true
            });

            searchControl.events.add('resultshow', function (e) {
                var index = e.get('index');
                searchControl.getResult(index).then(function (placeMark) {
                    setBalloonContent(placeMark);
                    myMap.geoObjects.add(placeMark);
                    selectPlaceMark(placeMark, 1);
                }, this);
            });

            var geolocationControl = myMap.controls.get('geolocationControl');
            geolocationControl.options.set({
                noPlacemark: true
            });

            geolocationControl.events.add('locationchange', function (e) {
                var collection = e.get('geoObjects');
                var placeMark = collection.get(0);
                setBalloonContent(placeMark);
                myMap.geoObjects.add(placeMark);
                selectPlaceMark(placeMark, 1);
            });

            $.each(data.placemarks, function (index, value) {
                if (value.coords) {
                    var placeMark = new ymaps.Placemark(value.coords);
                    setBalloonContent(placeMark, value.text);
                    myMap.geoObjects.add(placeMark);
                    if (data.center) {
                        if (value.coords[0] == data.center[0] && value.coords[1] == data.center[1]) {
                            selectPlaceMark(placeMark, 1);
                        }
                    }
                }
            });

            //myMap.setBounds(myMap.geoObjects.getBounds());

            myMap.geoObjects.events.add('click', function (e) {
                selectPlaceMark(e.get('target'), 0);
            });

            // myMap.balloon.events.add('close', function (e) {
            //
            // });

            myMap.events.add('dblclick', function (e) {
                // console.log(e.get('target'));
                // console.log(e.get('zoom'));
                // console.log(e.get('type'));
                // console.log(e.get('coords'));

                var coords = e.get('coords');
                var placeMark = new ymaps.Placemark(coords);
                setBalloonContent(placeMark, '');
                myMap.geoObjects.add(placeMark);
                selectPlaceMark(placeMark, 1);

            });


            $el.on('click', '.sp-placemark-delete', function (e) {
                e.preventDefault();

                if (myPlacemark) {
                    myMap.geoObjects.remove(myPlacemark);
                    myPlacemark = null;
                }
            });

            $el.on('change', '.sp-placemark-text', function () {
                if (myPlacemark) {
                    setBalloonContent(myPlacemark, $(this).val());
                }
            });

        });
    }

    function selectPlaceMark(placeMark, openBalloon) {
        if (myPlacemark) {
            myPlacemark.options.set({
                preset: 'islands#blueIcon'
            });
        }
        placeMark.options.set({
            preset: 'islands#redIcon'
        });
        myPlacemark = placeMark;

        if (openBalloon == 1) {
            myPlacemark.balloon.open();
        }
    }

    function setBalloonContent(placeMark, text) {
        var coords = placeMark.geometry.getCoordinates();

        text = $.trim(text);

        var content = sprint_editor.renderTemplate('yandex_map-placemark', {
            text: text
        });

        placeMark.properties.set({
            balloonContent: text,
            balloonContentHeader: '',
            balloonContentBody: content,
            balloonContentFooter: coords.join(' / ')
        });
    }

    function getBalloonContent(placeMark) {
        return placeMark.properties.get('balloonContent');
    }
});
