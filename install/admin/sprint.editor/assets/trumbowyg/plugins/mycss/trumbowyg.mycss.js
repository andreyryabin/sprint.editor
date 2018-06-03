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
        cssList: ['my-class-1', 'my-class-2', 'my-class-3', 'my-class-4']
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

                    $.each(trumbowyg.o.plugins.mycss.cssList, function (index, cssName) {
                        if ($(element).hasClass(cssName)) {

                            tags.push('mycssAdd' + cssName);
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

        $.each(trumbowyg.o.plugins.mycss.cssList, function (i, cssName) {
            var btn = 'mycssAdd' + cssName;

            trumbowyg.addBtnDef(btn, {
                text: cssName,
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
