sprint_editor.registerBlock('table', function ($, $el, data) {

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

        $el.find('tr').each(function () {
            var cols = [];
            $(this).find('td').each(function () {
                var col = {};
                var attrs = [];

                col.text = $(this).text();
                col.text = $.trim(col.text);

                if ($(this).hasClass('center')) {
                    attrs.push('center');
                } else if ($(this).hasClass('right')) {
                    attrs.push('right');
                }

                if ($(this).hasClass('bold')) {
                    attrs.push('bold');
                }

                if ($(this).attr('colspan')) {
                    col.colspan = $(this).attr('colspan');
                }

                if ($(this).attr('rowspan')) {
                    col.rowspan = $(this).attr('rowspan');
                }

                if (attrs.length > 0) {
                    col.attrs = attrs;
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
        var $table = $el.find('table');
        showcolbtns();

        $el.on('click', 'td', function () {
            $el.find('td').not(this).removeClass('active');
            $(this).addClass('active');
            showcolbtns();
        });

        $el.on('click', '.sp-add-col', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');
            if ($td.length > 0) {
                $('<td contenteditable="true"></td>').insertAfter($td);

                $td.next('td').trigger('click');
            }
            showcolbtns();
        });

        $el.on('click', '.sp-del-col', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');
            var $tr = $td.parent();

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

            var $td = $el.find('td.active');

            if ($td.length > 0) {
                $td.parent().remove();
            }

            showcolbtns();
        });
        $el.on('click', '.sp-sel-row', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');

            if ($td.length > 0) {
                $td.parent().toggleClass('active');
            }

            showcolbtns();
        });
        $el.on('click', '.sp-add-cs', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');

            if ($td.length > 0) {

                var $ntd = $td.next('td');

                if ($ntd.length) {

                    var ncs = $ntd.attr('colspan');
                    ncs = (ncs) ? parseInt(ncs, 10) : 1;

                    $ntd.remove();
                    var cs = $td.attr('colspan');

                    cs = (cs) ? parseInt(cs, 10) : 1;

                    $td.attr('colspan', cs + ncs);
                }
            }
            showcolbtns();
        });

        $el.on('click', '.sp-del-cs', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');

            if ($td.length > 0) {

                var cs = $td.attr('colspan');
                cs = (cs) ? parseInt(cs, 10) : 1;

                if (cs - 1 > 1) {
                    $td.attr('colspan', cs - 1);
                } else {
                    $td.removeAttr('colspan');
                }

                if (cs > 1) {
                    $('<td contenteditable="true"></td>').insertAfter($td);
                }
            }
            showcolbtns();
        });


        $el.on('click', '.sp-add-rs', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');

            if ($td.length > 0) {

                var cs = $td.attr('rowspan');

                cs = (cs) ? parseInt(cs, 10) : 1;

                $td.attr('rowspan', cs + 1);


            }
            showcolbtns();
        });

        $el.on('click', '.sp-del-rs', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');

            if ($td.length > 0) {

                var rs = $td.attr('rowspan');

                rs = (rs) ? parseInt(rs, 10) : 1;

                if (rs - 1 <= 1) {
                    $td.removeAttr('rowspan');
                } else {
                    $td.attr('rowspan', rs - 1);
                }

            }
            showcolbtns();
        });

        $el.on('click', '.sp-sel-dn', function (e) {
            var $trs = $el.find('tr.active');
            if ($trs.length > 0) {
                var $first = $trs.first();
                var $last = $trs.last();

                var $ntr = $last.next('tr');
                if (!$ntr.length) {
                    $ntr = addrowAppend($last);
                }

                $trs.insertAfter($ntr);
            }
        });


        $el.on('click', '.sp-sel-up', function (e) {
            var $trs = $el.find('tr.active');
            if ($trs.length > 0) {
                var $first = $trs.first();
                var $last = $trs.last();
                var $ntr = $first.prev('tr');
                $trs.insertBefore($ntr);
            }
        });


        $el.on('click', '.sp-toggle-align', function (e) {
            e.preventDefault();

            var $td = $el.find('td.active');

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

            var $td = $el.find('td.active');

            if ($td.length > 0) {
                if ($td.hasClass('bold')) {
                    $td.removeClass('bold');
                } else {
                    $td.addClass('bold');
                }
            }

            showcolbtns();
        });

        function addrow() {
            var $tr = $el.find('td.active').parent();
            if ($tr.length <= 0) {
                $tr = $table.find('tr').last();
            }

            var colCount = 0;
            $tr.find('td').each(function () {
                if ($(this).attr('colspan')) {
                    colCount += +$(this).attr('colspan');
                } else {
                    colCount++;
                }
            });

            colCount = (colCount > 0) ? colCount : 1;

            var newtr = '';
            for (var index = 1; index <= colCount; index++) {
                newtr += '<td contenteditable="true"></td>';
            }

            if ($tr.length > 0) {
                $('<tr>' + newtr + '</tr>').insertAfter($tr);
            } else {
                $table.append('<tr>' + newtr + '</tr>');
            }
        }

        function addrowAppend($tr) {
            var colCount = 0;
            $tr.find('td').each(function () {
                if ($(this).attr('colspan')) {
                    colCount += +$(this).attr('colspan');
                } else {
                    colCount++;
                }
            });

            colCount = (colCount > 0) ? colCount : 1;

            var newtr = '';
            for (var index = 1; index <= colCount; index++) {
                newtr += '<td contenteditable="true"></td>';
            }
            $table.append('<tr>' + newtr + '</tr>');

            return $table.find('tr').last();

        }

        function showcolbtns() {
            var $td = $table.find('td.active');

            $el.find('.sp-add-row').show();

            var $trSel = $table.find('tr.active');
            if ($trSel.length > 0) {
                $el.find('.sp-sel-up').show();
                $el.find('.sp-sel-dn').show();
            } else {
                $el.find('.sp-sel-up').hide();
                $el.find('.sp-sel-dn').hide();
            }

            if ($td.length > 0) {
                $el.find('.sp-col-buttons').show();
                $el.find('.sp-del-row').show();
                $el.find('.sp-sel-row').show();

                var $tr = $td.parent();
                var $ntd = $td.next('td');
                var rs = $td.attr('rowspan');
                rs = (rs) ? parseInt(rs, 10) : 1;

                var trindex = $table.find('tr').index($tr);
                var $ntr = $table.find('tr').eq(trindex + rs);


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

    };

});
