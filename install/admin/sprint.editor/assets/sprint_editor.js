var sprint_editor = {
    _templates: {},
    _parameters: {},
    _tplcache: {},

    _registered: {},

    registerBlock: function (name, method) {
        this._registered[name] = method;
    },

    registerTemplates: function (templates) {
        this._templates = templates
    },

    registerParameters: function (parameters) {
        this._parameters = parameters
    },

    registerTemplate: function (name, html) {
        this._templates[name] = html;
    },

    hasBlockMethod: function (name) {
        return !!this._registered[name];
    },

    getBlockMethod: function (name) {
        return (this._registered[name]) ? this._registered[name] : false;
    },

    hasBlockParams: function (name) {
        return !!this._parameters[name];
    },

    getBlockParams: function (name) {
        return (this._parameters[name]) ? this._parameters[name] : false;
    },

    getBlockWebPath: function (blockName) {
        var values = this.getBlockParams(blockName);
        return '/bitrix/admin/sprint.editor/' + values.group.name + '/' + values.name;
    },

    renderTemplate: function (name, data) {
        if (window.doT) {
            name = (this._templates[name]) ? name : 'dump';
            if (!this._tplcache[name]) {
                this._tplcache[name] = window.doT.template(this._templates[name]);
            }

            var tempfn = this._tplcache[name];
            return tempfn(data);
        } else {
            return '';
        }
    }
};


function sprint_editor_create($, params) {
    var $addblockinput = $('.j-addblock' + params.uniqid);
    var $blocks = $('.j-blocks' + params.uniqid);

    var $form = $blocks.closest('form').first();

    var collectionList = [];

    var layoutLastName = '';
    var layoutLastColCnt = 0;

    var layoutsCounter = 0;

    if (!params.jsonBlocks) {
        params.jsonBlocks = [];
    }

    if (!params.jsonLayouts) {
        params.jsonLayouts = {};
    }

    $.each(params.jsonLayouts, function(name, columns){
        layoutAdd(name, columns);
    });

    $.each(params.jsonBlocks, function(index, data){
        blockPush(data);
    });


    $('.j-layout-toggle' + params.uniqid).on('click', function () {
        if ($blocks.hasClass('sp-layout-mode')) {
            $blocks.removeClass('sp-layout-mode');
        } else {
            $blocks.addClass('sp-layout-mode');
        }

        toggleSizes();
    });

    $('.j-layout-remove' + params.uniqid).on('click', function () {
        layoutRemoveEmpty();
    });

    $form.on('submit', function (e) {
        var blocks = [];
        $.each(collectionList, function (index, entry) {
            var data = entry.collectData();
            if (typeof entry.getAreas == 'function') {
                var areas = entry.getAreas();
                $.each(areas, function (areaIndex, area) {
                    data[area.dataKey] = area.block.collectData()
                })
            }

            var $block = $blocks.find('.j-box').eq(index);
            var col = $block.closest('.sp-y-col').data();

            data.layout = {
                name: col.name,
                column: col.column
            };

            blocks.push(data);

        });

        var resultString = '';
        if (blocks.length > 0) {

            var post = {
                blocks: blocks,
                layouts: params.jsonLayouts
            };

            resultString = JSON.stringify(post);
            resultString = resultString.replace(/\\n/g, "\\n")
                .replace(/\\'/g, "\\'")
                .replace(/\\"/g, '\\"')
                .replace(/\\&/g, "\\&")
                .replace(/\\r/g, "\\r")
                .replace(/\\t/g, "\\t")
                .replace(/\\b/g, "\\b")
                .replace(/\\f/g, "\\f");
        }

        $blocks.find('input,textarea,select').removeAttr('name');
        $('.j-result' + params.uniqid).val(resultString);
    });

    if (params.enableChange) {

        $addblockinput.on('click', function (e) {
            var name = $('.j-selectblock' + params.uniqid).val();
            if (name.indexOf('layout_') === 0) {
                name = name.substr(7);
                layoutEmptyAdd(name);
            } else {
                blockEmptyPush(name);
            }
        });

        $blocks.on('click', '.j-upbox', function (e) {
            e.preventDefault();
            var index = $blocks.find('.j-upbox').index(this);
            var block = $(this).closest('.j-box');

            var nblock = block.prev();
            if (nblock.length > 0) {
                var nindex = nblock.index();

                block.insertBefore(nblock);
                collectionSwap(index, nindex);
            }
        });

        $blocks.on('click', '.j-dnbox', function (e) {
            e.preventDefault();
            var index = $blocks.find('.j-dnbox').index(this);
            var block = $(this).closest('.j-box');

            var nblock = block.next();
            if (nblock.length > 0) {
                var nindex = nblock.index();

                block.insertAfter(nblock);
                collectionSwap(index, nindex);
            }
        });

        $blocks.on('click', '.j-delbox', function (e) {
            e.preventDefault();

            var index = $blocks.find('.j-delbox').index(this);
            var $box = $(this).closest('.j-box');

            collectionRemove(index);
            $box.remove();

        });

        $blocks.on('click', '.sp-y-sizes span', function (e) {
            //
            // colSize = $.trim(colSize);

            var $span = $(this);

            if ($span.hasClass('active')) {
                toggleSizes();
                return;
            }

            var $xcol = $span.closest('.sp-x-col');
            var $cursize = $xcol.find('.sp-y-cursize');
            var $sizes = $xcol.find('.sp-y-sizes');


            $span.siblings('span').removeClass('active');
            $span.addClass('active');


            var result = [];
            $sizes.find('.active').each(function () {
                var tmp = $(this).text();
                tmp = $.trim(tmp);
                if (tmp.indexOf('col-') === 0) {
                    result.push(tmp);
                }
            });

            $cursize.text(
                result.join(', ')
            );

        });

        $blocks.on('click', '.sp-y-title', function (e) {
            toggleSizes($(this));
        });
    }

    function layoutEmptyAdd(colCnt) {
        layoutsCounter++;
        var name = 'A' + layoutsCounter;

        var columns = [];
        for (var index = 0; index < colCnt; index++) {
            columns.push('');
        }


        params.jsonLayouts[name] = columns;
        layoutAdd(name);
    }

    function layoutAdd(name, columns) {

        var sizes = [];
        for (var index = 1; index <= 12; index++) {
            sizes.push({
                size: index,
                active: 0
            });
        }

        console.log(columns);

        var html = sprint_editor.renderTemplate('box-layout', {
            columns: columns,
            sizes: sizes,
            name: name
        });

        $blocks.append(html);

        if (params.enableChange) {

            var $lastCols = $blocks.find('.sp-x-table').last().find('.sp-y-col');
            var startIndex = 0;
            var stopIndex = 0;

            $lastCols.sortable({
                connectWith: ".sp-y-col",
                handle: ".j-box_handle",
                placeholder: "sp-placeholder",
                start: function (event, ui) {
                    startIndex = $blocks.find('.j-box').index(
                        ui.item.get(0)
                    );
                },
                stop: function (event, ui) {
                    stopIndex = $blocks.find('.j-box').index(
                        ui.item.get(0)
                    );

                    collectionSwap(startIndex, stopIndex);
                }
            });

        }

    }

    function blockEmptyPush(name) {

        if (!layoutLastName) {
            layoutEmptyAdd(1);
        }

        blockPush({
            name: name,
            layout: {
                name: layoutLastName,
                column: layoutLastColCnt
            }

        });
    }

    function blockPush(data) {
        if (!data.name || !sprint_editor.hasBlockParams(data.name)) {
            return false;
        }

        var $layout = $blocks.find('.sp-x-table').last();
        var $column = $layout.find('.sp-y-col').eq(data.layout.column - 1);

        var templateVars = sprint_editor.getBlockParams(data.name);
        templateVars.showSortButtons = params.showSortButtons;
        templateVars.enableChange = params.enableChange;

        var html = sprint_editor.renderTemplate('box', templateVars);

        $column.append(html);

        var $el = $column.find('.j-box-block').last();
        var entry = blockInit($el, data.name, data);

        if (typeof entry.getAreas == 'function') {
            var areas = entry.getAreas();
            var entryData = entry.getData();

            $.each(areas, function (areaIndex, area) {
                if (data.name != area.blockName) {
                    area.block = blockInit($el.find(area.container), area.blockName, entryData[area.dataKey]);
                }
            });
        }

        collectionList.push(entry);
    }

    function toggleSizes($title) {
        if ($title) {
            var $sizes = $title.closest('.sp-x-col').find('.sp-y-sizes');
            $blocks.find('.sp-y-sizes').not($sizes).hide();
            $blocks.find('.sp-y-title').not($title).removeClass('active');

            if ($sizes.is(':hidden')) {
                $sizes.show();
                $title.addClass('active');
            } else {
                $sizes.hide();
                $title.removeClass('active');
            }

        } else {
            $blocks.find('.sp-y-sizes').hide();
            $blocks.find('.sp-y-title').removeClass('active');
        }
    }

    function blockInit($el, name, data) {
        name = sprint_editor.hasBlockMethod(name) ? name : 'dump';

        var method = sprint_editor.getBlockMethod(name);
        var entry = new method($, $el, data);

        var html = sprint_editor.renderTemplate(name, entry.getData());
        $el.html(html).addClass('sp-block-' + name);

        if (typeof entry.afterRender == 'function') {
            entry.afterRender();
        }

        return entry;
    }

    function collectionSwap(indexA, indexB) {
        if (indexA !== indexB) {
            var tempA = collectionList[indexA];
            collectionList.splice(indexA, 1);
            collectionList.splice(indexB, 0, tempA);
        }
    }

    function collectionRemove(index) {
        collectionList.splice(index, 1);
    }

    function layoutRemoveEmpty() {
        $blocks.find('.sp-x-table').each(function () {
            var colCnt = $(this).find('.sp-y-col').length;
            var colEmp = 0;
            $(this).find('.sp-y-col').each(function () {
                if ($(this).is(':empty')) {
                    colEmp++;
                }
            });
            if (colCnt == colEmp) {
                $(this).remove();
            }
        });

        var $lastCol = $blocks.find('.sp-y-col').last();
        if ($lastCol.length) {
            layoutLastName = $lastCol.data('name');
        } else {
            layoutLastName = '';
        }
    }
}
