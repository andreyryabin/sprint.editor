sprint_editor.registerBlock('table', function ($, $el, data, settings) {

    data = $.extend({
        rows: [
            [{text: ''}, {text: ''}, {text: ''}, {text: ''}],
            [{text: ''}, {text: ''}, {text: ''}, {text: ''}]
        ]

    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.rows = [];

        let collectClass = (settings.tdlist && settings.tdlist.value);

        $el.find('tr').each(function () {
            let cols = [];
            $(this).find('td').each(function () {
                let col = {};

                let teditor = $(this).find('.trumbowyg-editor').first();
                if (teditor.length > 0) {
                    col.text = teditor.trumbowyg('html');
                } else {
                    let tbox = $(this).find('.trumbowyg-editor-box').first();
                    if (tbox.length > 0) {
                        col.text = tbox.html();
                    } else {
                        col.text = '';
                    }
                }

                if (collectClass) {
                    let $fake = $('<div>').attr('class', $(this).attr('class'));
                    $fake.removeClass('inited').removeClass('active');
                    col.class = $fake.attr('class');
                }

                if ($(this).attr('colspan')) {
                    col.colspan = $(this).attr('colspan');
                }

                if ($(this).attr('rowspan')) {
                    col.rowspan = $(this).attr('rowspan');
                }

                cols.push(col)
            });

            if (cols.length > 0) {
                data.rows.push(cols);
            }

        });

        return data;
    };

    this.afterRender = function () {
        let $table = $el.find('table');
        let $editor = null;

        let $fake = $el.find('.fake-button-pane');

        showcolbtns();

        $el.on('keydown', function (e) {
            let keyCode = e.keyCode || e.which;
            if (keyCode === 9) {
                e.preventDefault();
                let $td = $el.find('td.active');
                if ($td.next('td').length > 0) {
                    $td.next('td').trigger('click');
                } else {
                    let $nexttd = $td.closest('tr').next('tr').find('td').first();
                    if ($nexttd.length > 0) {
                        $nexttd.trigger('click');
                    }

                }
            }
        });

        $el.on('click', 'td', function () {
            $el.find('td').not(this).removeClass('active');
            $(this).addClass('active');
            showcolbtns();
        });

        $el.on('click', '.sp-add-col', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');
            addcol($td, true)
            showcolbtns();
        });

        $el.on('click', '.sp-del-col', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');
            let $tr = $td.parent();

            if ($td.length > 0) {
                if ($tr.find('td').length - 1 > 0) {
                    $td.next('td').trigger('click');
                    $td.remove();
                } else {
                    $tr.remove();
                }
            }
            showcolbtns();
        });

        $el.on('click', '.sp-add-row', function (e) {
            e.preventDefault();
            addrow();
            showcolbtns();
        });

        $el.on('click', '.sp-del-row', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {
                $td.parent().remove();
            }

            showcolbtns();
        });

        $el.on('click', '.sp-sel-row', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {
                $td.parent().toggleClass('active');
            }

            showcolbtns();
        });

        $el.on('click', '.sp-add-cs', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {

                let $ntd = $td.next('td');

                if ($ntd.length) {

                    let ncs = $ntd.attr('colspan');
                    ncs = (ncs) ? parseInt(ncs, 10) : 1;

                    $ntd.remove();
                    let cs = $td.attr('colspan');

                    cs = (cs) ? parseInt(cs, 10) : 1;

                    $td.attr('colspan', cs + ncs);
                }
            }
            showcolbtns();
        });

        $el.on('click', '.sp-del-cs', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {

                let cs = $td.attr('colspan');
                cs = (cs) ? parseInt(cs, 10) : 1;

                if (cs - 1 > 1) {
                    $td.attr('colspan', cs - 1);
                } else {
                    $td.removeAttr('colspan');
                }

                if (cs > 1) {
                    addcol($td, false);
                }
            }
            showcolbtns();
        });

        $el.on('click', '.sp-add-rs', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {

                let rs = $td.attr('rowspan');

                rs = (rs) ? parseInt(rs, 10) : 1;

                $td.attr('rowspan', rs + 1);
            }
            showcolbtns();
        });

        $el.on('click', '.sp-del-rs', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {

                let rs = $td.attr('rowspan');

                rs = (rs) ? parseInt(rs, 10) : 1;

                if (rs - 1 <= 1) {
                    $td.removeAttr('rowspan');
                } else {
                    $td.attr('rowspan', rs - 1);
                }
            }
            showcolbtns();
        });

        $el.on('click', '.sp-sel-dn', function () {
            let $trs = $el.find('tr.active');
            if ($trs.length > 0) {
                let $last = $trs.last();

                let $ntr = $last.next('tr');
                if (!$ntr.length) {
                    $ntr = addrowAppend($last);
                }

                $trs.insertAfter($ntr);
            }
        });

        $el.on('click', '.sp-sel-up', function () {
            let $trs = $el.find('tr.active');
            if ($trs.length > 0) {
                let $first = $trs.first();
                let $ntr = $first.prev('tr');
                $trs.insertBefore($ntr);
            }
        });

        $el.on('click', '.sp-toggle-align', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {
                if ($td.hasClass('center')) {
                    $td.removeClass('center');
                    $td.addClass('right');
                } else if ($td.hasClass('right')) {
                    $td.removeClass('right');
                } else {
                    $td.addClass('center');
                }
            }
            showcolbtns();
        });

        $el.on('click', '.sp-toggle-bold', function (e) {
            e.preventDefault();

            let $td = $el.find('td.active');

            if ($td.length > 0) {
                if ($td.hasClass('bold')) {
                    $td.removeClass('bold');
                } else {
                    $td.addClass('bold');
                }
            }

            showcolbtns();
        });

        function addcol($td, select) {
            if ($td.length > 0) {
                $('<td><div class="trumbowyg-editor-box"></div></td>').insertAfter($td);

                if (select) {
                    $td.next('td').trigger('click');
                }
            }
        }

        function colcount($tr) {
            let cnt = 0;
            $tr.find('td').each(function () {
                if ($(this).attr('colspan')) {
                    cnt += +$(this).attr('colspan');
                } else {
                    cnt++;
                }
            });

            return cnt;
        }

        function addrow() {
            let $tr = $el.find('td.active').parent();
            if ($tr.length <= 0) {
                $tr = $table.find('tr').last();
            }

            let colCount = colcount($tr) || 4;

            let newtr = '';
            for (let index = 1; index <= colCount; index++) {
                newtr += '<td><div class="trumbowyg-editor-box"></div></td>';
            }

            if ($tr.length > 0) {
                $('<tr>' + newtr + '</tr>').insertAfter($tr);
            } else {
                $table.append('<tr>' + newtr + '</tr>');
            }
        }


        function addrowAppend($tr) {
            let colCount = colcount($tr)

            let newtr = '';
            for (let index = 1; index <= colCount; index++) {
                newtr += '<td><div class="trumbowyg-editor-box"></div></td>';
            }
            $table.append('<tr>' + newtr + '</tr>');

            return $table.find('tr').last();

        }

        function showcolbtns() {
            let $td = $table.find('td.active');

            $el.find('.sp-add-row').show();

            let $trSel = $table.find('tr.active');
            if ($trSel.length > 0) {
                $el.find('.sp-sel-up').show();
                $el.find('.sp-sel-dn').show();
            } else {
                $el.find('.sp-sel-up').hide();
                $el.find('.sp-sel-dn').hide();
            }

            if ($td.length > 0) {
                initeditor($td);

                $el.find('.sp-col-buttons').show();
                $el.find('.sp-del-row').show();
                $el.find('.sp-sel-row').show();

                let $tr = $td.parent();
                let $ntd = $td.next('td');
                let rs = $td.attr('rowspan');
                rs = (rs) ? parseInt(rs, 10) : 1;

                let trindex = $table.find('tr').index($tr);
                let $ntr = $table.find('tr').eq(trindex + rs);

                if ($tr.find('td').length > 1) {
                    $el.find('.sp-del-col').show();
                } else {
                    $el.find('.sp-del-col').hide();
                }

                if ($ntd.length > 0) {
                    $el.find('.sp-add-cs').show();
                } else {
                    $el.find('.sp-add-cs').hide();
                }

                if ($td.attr('colspan')) {
                    $el.find('.sp-del-cs').show();
                } else {
                    $el.find('.sp-del-cs').hide();
                }

                if ($td.attr('rowspan')) {
                    $el.find('.sp-del-rs').show();
                } else {
                    $el.find('.sp-del-rs').hide();
                }

                if ($ntr.length > 0) {
                    $el.find('.sp-add-rs').show();
                } else {
                    $el.find('.sp-add-rs').hide();
                }
            } else {
                $el.find('.sp-col-buttons').hide();
                $el.find('.sp-del-row').hide();
                $el.find('.sp-sel-row').hide();
            }
        }


        function initeditor($cell) {

            if ($cell.hasClass('inited')) {
                return;
            }

            $cell.addClass('inited');

            if ($editor) {
                $editor.trumbowyg('destroy');
                $editor = null;
            }

            $el.find('td').not($cell).each(function () {
                $(this).removeClass('inited');
            });

            $editor = $cell.children('.trumbowyg-editor-box').first();

            let btns = [
                ['viewHTML', 'strong', 'em', 'underline', 'del', 'link'],
                ['justifyLeft', 'justifyCenter', 'justifyRight']
            ];

            let plugins = {};

            if (settings.csslist && settings.csslist.value) {
                btns.push(['mycss']);
                plugins['mycss'] = {csslist: settings.csslist.value}
            }

            if (settings.tdlist && settings.tdlist.value) {
                btns.push(['mytdcss']);
                plugins['mytdcss'] = {tdlist: settings.tdlist.value};
            }

            $editor.trumbowyg({
                svgPath: '/bitrix/admin/sprint.editor/assets/trumbowyg/ui/icons.svg',
                lang: 'ru',
                resetCss: false,
                removeformatPasted: true,
                autogrow: true,
                btns: btns,
                plugins: plugins
            }).focus();

            $fake.height(
                $cell.find('.trumbowyg-button-pane').height()
            );
        }
    };

});
