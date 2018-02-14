(function ($) {
    'use strict';

    // My plugin default options
    var defaultOptions = {
    };

    // If my plugin is a button
    function buildButtonDef(trumbowyg) {
        return {
            fn: function() {
                // My plugin button logic
            }
        }
    }

    $.extend(true, $.trumbowyg, {
        // Add some translations
        langs: {
            ru: {
                mycss: 'My Css'
            }
        },
        // Add our plugin to Trumbowyg registred plugins
        plugins: {
            mycss: {
                init: function(trumbowyg) {
                    // Fill current Trumbowyg instance with my plugin default options
                    trumbowyg.o.plugins.mycss = $.extend(true, {},
                        defaultOptions,
                        trumbowyg.o.plugins.mycss || {}
                    );

                    trumbowyg.addBtnDef('mycss', {
                        dropdown: makeDropdown(trumbowyg),
                        hasIcon: false,
                        text: trumbowyg.lang.mycss
                    });

                }
            }
        }
    });

    function makeDropdown(trumbowyg) {
        var cssList = ['x1','x2','x3'];

        var buttons = [];

        $.each(cssList, function(index, cssName) {
            buttons.push('mycss_' + index);
            
            trumbowyg.addBtnDef('mycss_' + index, {
                hasIcon: false,
                title: cssName,
                fn:function(){

                    trumbowyg.saveRange();
                    //trumbowyg.getRangeText()

                    console.log(
                        trumbowyg.range
                    );



                    var node = $('<span>1122233</span>')[0];
                    trumbowyg.range.deleteContents();
                    trumbowyg.range.insertNode(node);



                    return true;

                }
            });
        });

        return buttons;
    }


})(jQuery);