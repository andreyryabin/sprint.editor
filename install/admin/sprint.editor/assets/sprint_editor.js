var sprint_editor = {
    _templates: {},
    _parameters: {},
    _tplcache: {},
    _varscahe: {},

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
    },

    setCacheVar: function (name, value) {
        this._varscahe[name] = value;
    },

    getCacheVar: function (name) {
        var value = '';
        if (this._varscahe[name]) {
            value = this._varscahe[name];
        }
        return value;
    }
};


function sprint_editor_create($, params) {
    var $addblockinput = $('.j-addblock' + params.uniqid);
    var $editor = $('.j-editor' + params.uniqid);
    var $blocks = $('.j-blocks' + params.uniqid);
    var $buttons = $('.j-buttons' + params.uniqid);

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


    toggleLayoutButtons();


    $form.on('submit', function (e) {

        var blocks = [];
        var layouts = [];

        var index = 0;

        $blocks.find('.sp-x-table').each(function (pos1) {
            var columns = [];

            $(this).find('.sp-x-col').each(function(pos2){

                var text = $(this).find('.sp-x-ctype').text();
                columns.push(text);

                $(this).find('.j-box').each(function(){

                    var bdata = collectionCollect(index);
                    bdata.layout = pos1 + ',' + pos2;

                    blocks.push(bdata);
                    index++;
                });

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

        if (getLocal('mode') === '1') {
            $editor.addClass('sp-layout-mode');
        } else {
            $editor.removeClass('sp-layout-mode');
        }

        $('.j-layout-toggle' + params.uniqid).on('click', function () {
            if ($editor.hasClass('sp-layout-mode')) {
                $editor.removeClass('sp-layout-mode');
                setLocal('mode', '');
            } else {
                $editor.addClass('sp-layout-mode');
                setLocal('mode', '1');
            }
        });

        $('.j-layout-remove' + params.uniqid).on('click', function (e) {
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
            toggleLayoutButtons();
        });

        $blocks.on('click', '.j-upbox', function (e) {
            e.preventDefault();
            var index = $blocks.find('.j-upbox').index(this);
            var block = $(this).closest('.j-box');

            var nblock = block.prev('.j-box');
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

            var nblock = block.next('.j-box');
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

        $blocks.on('click', '.sp-x-types span', function (e) {
            var $span = $(this);

            var $xcol = $span.closest('.sp-x-col');
            var $cursize = $xcol.find('.sp-x-ctype');
            var $sizes = $xcol.find('.sp-x-types');

            $span.siblings('span').removeClass('active');

            if ($span.hasClass('active')) {
                $span.removeClass('active');
            } else {
                $span.addClass('active');
            }

            var result = [];
            $sizes.find('.active').each(function () {
                var tmp = $(this).text();
                tmp = $.trim(tmp);
                result.push(tmp);
            });

            $cursize.text(
                result.join(',')
            );

        });

        $blocks.on('click', '.sp-x-title', function (e) {
            var $title = $(this);
            var $xcol = $title.closest('.sp-x-col');
            var $sizes = $xcol.find('.sp-x-types');
            if ($title.hasClass('active')){
                $sizes.hide();
                $title.removeClass('active');
            } else {
                $blocks.find('.sp-x-types').not($sizes).hide();
                $blocks.find('.sp-x-title').not($title).removeClass('active');

                var cursizes = $xcol.find('.sp-x-ctype').text();
                cursizes = cursizes.split(',');
                $sizes.find('span').each(function () {
                    var stext = $(this).text();
                    if ($.inArray(stext, cursizes) >= 0) {
                        $(this).addClass('active');
                    }
                });

                $sizes.show();
                $title.addClass('active');
            }

        });
    }

    function layoutEmptyAdd(colCnt) {
        var columns = [];
        var size = '';

        if (colCnt == 2){
            size = 'md-6';
        } else if (colCnt == 3){
            size = 'md-4';
        } else if (colCnt == 4){
            size = 'md-3';
        }

        for (var index = 1; index <= colCnt; index++) {
            columns.push(size)
        }

        layoutAdd(columns);
    }

    function layoutAdd(columns) {

        if (!sprint_editor.getCacheVar('layout-sizes')) {
            var types = ['md-', 'sm-', 'xs-', 'lg-'];
            var groups = [];
            for (var type in types) {
                var sizes = [];
                for (var index = 1; index <= 12; index++) {
                    sizes.push(types[type] + index);
                }
                groups.push(sizes);
            }
            var sizesHtml = sprint_editor.renderTemplate('box-sizes', {
                groups: groups
            });

            sprint_editor.setCacheVar('layout-sizes', sizesHtml);
        }

        var layoutHtml = sprint_editor.renderTemplate('box-layout', {
            sizeshtml: sprint_editor.getCacheVar('layout-sizes'),
            columns: columns,
            name: name
        });


        $blocks.append(layoutHtml);

        if (params.enableChange) {

            var $lastCols = $blocks.find('.sp-x-table').last().find('.sp-x-col');
            var startIndex = 0;
            var stopIndex = 0;

            $lastCols.sortable({
                items: ".j-box",
                connectWith: ".sp-x-col",
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
            }).disableSelection();

        }

    }

    function toggleLayoutButtons() {
        if ($blocks.find('.sp-x-col').length <= 0) {
            $buttons.hide();
        } else {
            $buttons.show();
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

        if ($blocks.find('.sp-x-col').length <= 0) {
            layoutEmptyAdd(1);
        }

        var $column;

        if (data.layout) {
            var pos = data.layout.split(',');

            var $layout = $blocks.find('.sp-x-table').eq(pos[0]);
            $column = $layout.find('.sp-x-col').eq(pos[1]);

        } else {
            $column = $blocks.find('.sp-x-col').last();
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

    function collectionCollect(index) {
        var entry = collectionList[index];

        var data = entry.collectData();
        if (typeof entry.getAreas === 'function') {
            var areas = entry.getAreas();
            $.each(areas, function (areaIndex, area) {
                data[area.dataKey] = area.block.collectData()
            })
        }

        return data;
    }

    function layoutRemoveEmpty() {
        $blocks.find('.sp-x-table').each(function (index) {
            var colCnt = 0;
            var colEmp = 0;
            $(this).find('.sp-x-col').each(function () {
                colCnt++;

                if ($(this).find('.j-box').length <= 0) {
                    colEmp++;
                }

            });
            if (colCnt === colEmp) {
                $(this).remove();
            }
        });

        toggleLayoutButtons();
    }

    function setLocal(key, val) {
        if (window.localStorage) {
            key = 'sp' + params.uniqid + key;
            localStorage.setItem(key, val);
        }
    }

    function getLocal(key) {
        var val = '';
        if (window.localStorage) {
            key = 'sp' + params.uniqid + key;
            val = localStorage.getItem(key);
        }
        return val;
    }

}
