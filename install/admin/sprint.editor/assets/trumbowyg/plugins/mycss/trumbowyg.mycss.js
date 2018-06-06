(function ($) {
    'use strict';

    $.extend(true, $.trumbowyg, {
        langs: {
            ru: {
                myCss: 'Стиль'
            },
            en: {
                myCss: 'Style'
            }


        }
    });

    // jshint camelcase:true


    var defaultOptions = {
        cssList: {
            'my-css-1' : 'MyCss1',
            'my-css-2' : 'MyCss2',
            'my-css-3' : 'MyCss3'
        }
    };

    // Add all colors in two dropdowns
    $.extend(true, $.trumbowyg, {
        plugins: {
            mycss: {
                init: function (trumbowyg) {
                    trumbowyg.o.plugins.mycss = trumbowyg.o.plugins.mycss || defaultOptions;
                    var myCssBtnDef = {
                        dropdown: buildDropdown(trumbowyg),
                        text: trumbowyg.lang.myCss,
                        hasIcon: false

                    };

                    trumbowyg.addBtnDef('myCss', myCssBtnDef);

                },
                tagHandler: function (element, trumbowyg) {
                    var tags = [];

                    $.each(trumbowyg.o.plugins.mycss.cssList, function (cssName, cssTitle) {
                        if ($(element).hasClass(cssName)) {

                            tags.push('mycss-' + cssName);
                            return true;
                        }
                    });

                    return tags;
                }
            }
        }
    });

    function buildDropdown(trumbowyg) {
        var dropdown = [];

        $.each(trumbowyg.o.plugins.mycss.cssList, function (cssName,cssTitle) {
            var btn = 'mycss-' + cssName;

            trumbowyg.addBtnDef(btn, {
                text: cssTitle,
                hasIcon: false,
                fn: function () {
                    trumbowyg.saveRange();
                    trumbowyg.execCmd(
                        'insertHTML',
                        '<span class="' + cssName + '">' + trumbowyg.getRangeText() + '</span>'
                    );
                }
            });

            dropdown.push(btn);
        });

        return dropdown;
    }
})(jQuery);
