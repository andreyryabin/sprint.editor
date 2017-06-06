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
    var $editor = $('.j-editor' + params.uniqid);
    var $blocks = $('.j-blocks' + params.uniqid);

    var $form = $blocks.closest('form').first();

    var collectionList = [];

    if (!params.jsonValue) {
        params.jsonValue = {};
    }

    if (!params.jsonValue.blocks) {
        params.jsonValue.blocks = [];
    }

    if (!params.jsonValue.layouts) {
        params.jsonValue.layouts = {};
    }

    $.each(params.jsonValue.layouts, function (index, columns) {
        layoutAdd(columns);
    });

    $.each(params.jsonValue.blocks, function (index, block) {
        blockPush(block);
    });

    $form.on('submit', function (e) {
        var blocks = [];
        var layouts = [];

        $.each(collectionList, function (index, entry) {
            var data = entry.collectData();
            if (typeof entry.getAreas == 'function') {
                var areas = entry.getAreas();
                $.each(areas, function (areaIndex, area) {
                    data[area.dataKey] = area.block.collectData()
                })
            }

            var $box = $blocks.find('.j-box').eq(index);

            var $layout = $box.closest('.sp-x-table');
            var $column = $box.closest('.sp-x-col');

            var pos1 = $blocks.find('.sp-x-table').index($layout);
            var pos2 = $layout.find('.sp-x-col').index($column);

            data.layout = pos1 + ',' + pos2;

            blocks.push(data);

        });


        $blocks.find('.sp-x-table').each(function (lindex) {
            var columns = [];
            $(this).find('.sp-y-ctype').each(function (cindex) {
                var text = $(this).text();
                columns.push(text);
            });
            layouts.push(columns);
        });

        var resultString = '';

        if (layouts.length > 0) {
            var post = {
                blocks: blocks,
                layouts: layouts
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

        $('.j-layout-toggle' + params.uniqid).on('click', function () {
            if ($editor.hasClass('sp-layout-mode')) {
                $editor.removeClass('sp-layout-mode');
            } else {
                $editor.addClass('sp-layout-mode');
            }
        });

        $('.j-layout-remove' + params.uniqid).on('click', function () {
            layoutRemoveEmpty();
        });

        $addblockinput.on('click', function (e) {
            var name = $('.j-selectblock' + params.uniqid).val();
            if (name.indexOf('layout_') === 0) {
                name = name.substr(7);
                layoutEmptyAdd(name);
            } else {
                blockPush({name: name});
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

        var sizesInterval;
        $blocks.on('mouseenter', '.sp-y-types', function () {
            clearInterval(sizesInterval);
        });

        $blocks.on('mouseleave', '.sp-y-types', function () {
            sizesInterval = setTimeout(function () {
                toggleSizes();
            }, 2000);

        });

        $blocks.on('click', '.sp-y-types span', function (e) {
            var $span = $(this);

            var $xcol = $span.closest('.sp-x-col');
            var $cursize = $xcol.find('.sp-y-ctype');
            var $sizes = $xcol.find('.sp-y-types');

            $span.siblings('span').removeClass('active');
            $span.addClass('active');

            var result = [];
            $sizes.find('.active').each(function () {
                if (!$(this).hasClass('sp-y-notype')) {
                    var tmp = $(this).text();
                    tmp = $.trim(tmp);
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
        var columns = [];
        for (var index = 1; index <= colCnt; index++) {
            columns.push('')
        }

        layoutAdd(columns);
    }

    function layoutAdd(columns) {
        var sizes = [];
        for (var index = 1; index <= 12; index++) {
            sizes.push({
                size: index,
                active: 0
            });
        }

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

    function blockPush(data) {
        if (!data.name || !sprint_editor.hasBlockParams(data.name)) {
            return false;
        }

        var templateVars = sprint_editor.getBlockParams(data.name);
        templateVars.showSortButtons = params.showSortButtons;
        templateVars.enableChange = params.enableChange;
        var html = sprint_editor.renderTemplate('box', templateVars);

        if ($blocks.find('.sp-y-col').length <= 0) {
            layoutEmptyAdd(1);
        }

        var $column;

        if (data.layout) {
            var pos = data.layout.split(',');

            var $layout = $blocks.find('.sp-x-table').eq(pos[0]);
            $column = $layout.find('.sp-y-col').eq(pos[1]);

        } else {
            $column = $blocks.find('.sp-y-col').last();
        }

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
            clearInterval(sizesInterval);

            var $sizes = $title.closest('.sp-x-col').find('.sp-y-types');
            $blocks.find('.sp-y-types').not($sizes).hide();
            $blocks.find('.sp-y-title').not($title).removeClass('active');

            if ($sizes.is(':hidden')) {
                $sizes.show();
                $title.addClass('active');
            } else {
                $sizes.hide();
                $title.removeClass('active');
            }

        } else {
            $blocks.find('.sp-y-types').hide();
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
        $blocks.find('.sp-x-table').each(function (index) {
            var colCnt = 0;
            var colEmp = 0;
            $(this).find('.sp-y-col').each(function () {
                colCnt++;
                if ($(this).is(':empty')) {
                    colEmp++;
                }
            });
            if (colCnt === colEmp) {
                $(this).remove();
            }
        });
    }
}
