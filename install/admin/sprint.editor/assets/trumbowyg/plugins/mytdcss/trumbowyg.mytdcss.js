(function ($) {
    'use strict';

    let defaultOptions = {
        tdlist: {'my-td-1': 'MyCss1'}
    };

    // Add all colors in two dropdowns
    $.extend(true, $.trumbowyg, {
        langs: {
            ru: {
                mytdcss: 'Стиль'
            },
            en: {
                mytdcss: 'Style'
            }
        },
        plugins: {
            mytdcss: {
                init: function (trumbowyg) {

                    trumbowyg.o.plugins.mytdcss = trumbowyg.o.plugins.mytdcss || defaultOptions;
                    let btnDef = {
                        dropdown: buildDropdown(trumbowyg),
                        text: trumbowyg.lang.mytdcss,
                        hasIcon: false
                    };

                    trumbowyg.addBtnDef('mytdcss', btnDef);

                },
                tagHandler: function (element, trumbowyg) {
                    let tags = [];
                    let $td = trumbowyg.$box.closest('td');

                    $.each(trumbowyg.o.plugins.mytdcss.tdlist, function (cssName, cssTitle) {
                        if ($td.hasClass(cssName)) {
                            tags.push('mytdcss-' + cssName);
                        }
                    });

                    console.log(tags);

                    return tags;
                }
            }
        }
    });

    function buildDropdown(trumbowyg) {
        let dropdown = [];

        $.each(trumbowyg.o.plugins.mytdcss.tdlist, function (cssName, cssTitle) {
            let btn = 'mytdcss-' + cssName;

            trumbowyg.addBtnDef(btn, {
                text: cssTitle,
                hasIcon: false,
                fn: function () {
                    trumbowyg.$box.closest('td').toggleClass(cssName);
                }
            });

            dropdown.push(btn);
        });

        return dropdown;
    }
})(jQuery);
