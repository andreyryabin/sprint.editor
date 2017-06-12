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
    var $editor = $('.sp-x-editor' + params.uniqid);

    var $buttons = $editor.find('.sp-x-buttons');
    var $boxes = $editor.find('.sp-x-boxes');

    var $form = $editor.closest('form').first();

    var collections = [];

    if (!params.jsonValue) {
        params.jsonValue = {};
    }

    if (!params.jsonValue.blocks) {
        params.jsonValue.blocks = [];
    }

    if (!params.jsonValue.layouts) {
        params.jsonValue.layouts = [];
    }

    $.each(params.jsonValue.layouts, function (index, columns) {
        layoutAdd(columns);
    });

    $.each(params.jsonValue.blocks, function (index, block) {
        pushblock(block);
    });


    toggleLayoutButtons();


    $form.on('submit', function (e) {

        var blocks = [];
        var layouts = [];

        var index = 0;

        $boxes.find('.sp-x-lt-yy').each(function (pos1) {
            var columns = [];

            $(this).find('.sp-x-lt-col').each(function(pos2){

                var text = $(this).find('.sp-x-lt-curtype').text();
                columns.push(text);

                $(this).find('.sp-x-box').each(function(){

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


        $boxes.find('input,textarea,select').removeAttr('name');
        $editor.find('.sp-x-result').val(resultString);
    });

    if (params.enableChange) {

        if (getLocal('mode') === '1') {
            $editor.addClass('sp-x-layout-mode');
        } else {
            $editor.removeClass('sp-x-layout-mode');
        }

        $editor.find('.sp-x-layout-toggle').on('click', function () {
            if ($editor.hasClass('sp-x-layout-mode')) {
                $editor.removeClass('sp-x-layout-mode');
                setLocal('mode', '');
            } else {
                $editor.addClass('sp-x-layout-mode');
                setLocal('mode', '1');
            }
        });

        $editor.find('.sp-x-layout-del').on('click', function (e) {
            layoutRemoveEmpty();
        });

        $editor.find('.sp-x-box-add').on('click', function (e) {
            var name = $editor.find('.sp-x-box-select').val();
            if (name.indexOf('layout_') === 0) {
                name = name.substr(7);
                layoutEmptyAdd(name);
            } else {
                pushblock({name: name});
            }
            toggleLayoutButtons();
        });

        $boxes.on('click', '.sp-x-box-up', function (e) {
            e.preventDefault();
            var index = $boxes.find('.sp-x-box-up').index(this);
            var block = $(this).closest('.sp-x-box');

            var nblock = block.prev('.sp-x-box');
            if (nblock.length > 0) {
                var nindex = nblock.index();

                block.insertBefore(nblock);
                collectionSwap(index, nindex);
            }
        });

        $boxes.on('click', '.sp-x-box-dn', function (e) {
            e.preventDefault();
            var index = $boxes.find('.sp-x-box-dn').index(this);
            var block = $(this).closest('.sp-x-box');

            var nblock = block.next('.sp-x-box');
            if (nblock.length > 0) {
                var nindex = nblock.index();

                block.insertAfter(nblock);
                collectionSwap(index, nindex);
            }
        });

        $boxes.on('click', '.sp-x-box-del', function (e) {
            e.preventDefault();

            var index = $boxes.find('.sp-x-box-del').index(this);
            var $box = $(this).closest('.sp-x-box');

            collectionRemove(index);
            $box.remove();
        });

        $boxes.on('click', '.sp-x-lt-types span', function (e) {
            var $span = $(this);

            var $xcol = $span.closest('.sp-x-lt-col');
            var $cursize = $xcol.find('.sp-x-lt-curtype');
            var $sizes = $xcol.find('.sp-x-lt-types');

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

        $boxes.on('click', '.sp-x-lt-title', function (e) {
            var $title = $(this);
            var $xcol = $title.closest('.sp-x-lt-col');
            var $sizes = $xcol.find('.sp-x-lt-types');
            if ($title.hasClass('active')){
                $sizes.hide();
                $title.removeClass('active');
            } else {
                $boxes.find('.sp-x-lt-types').not($sizes).hide();
                $boxes.find('.sp-x-lt-title').not($title).removeClass('active');

                var cursizes = $xcol.find('.sp-x-lt-curtype').text();
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


        $boxes.append(layoutHtml);

        if (params.enableChange) {

            var $lastCols = $boxes.find('.sp-x-lt-yy').last().find('.sp-x-lt-col');
            var startIndex = 0;
            var stopIndex = 0;

            $lastCols.sortable({
                items: ".sp-x-box",
                connectWith: ".sp-x-lt-col",
                handle: ".sp-x-box-handle",
                placeholder: "sp-x-box-placeholder",
                start: function (event, ui) {
                    startIndex = $boxes.find('.sp-x-box').index(
                        ui.item.get(0)
                    );
                },
                stop: function (event, ui) {
                    stopIndex = $boxes.find('.sp-x-box').index(
                        ui.item.get(0)
                    );

                    collectionSwap(startIndex, stopIndex);
                }
            }).disableSelection();

        }

    }

    function toggleLayoutButtons() {
        if ($boxes.find('.sp-x-lt-col').length <= 0) {
            $buttons.hide();
        } else {
            $buttons.show();
        }
    }

    function pushblock(data) {
        if (!data.name || !sprint_editor.hasBlockParams(data.name)) {
            return false;
        }

        var templateVars = sprint_editor.getBlockParams(data.name);
        templateVars.showSortButtons = params.showSortButtons;
        templateVars.enableChange = params.enableChange;
        var html = sprint_editor.renderTemplate('box', templateVars);

        if ($boxes.find('.sp-x-lt-col').length <= 0) {
            layoutEmptyAdd(1);
        }

        var $column = $boxes.find('.sp-x-lt-col').last();

        if (data.layout) {
            var pos = data.layout.split(',');
            $boxes.find('.sp-x-lt-yy').each(function (pos1) {
                if (pos1 == pos[0]){
                    $(this).find('.sp-x-lt-col').each(function(pos2){
                        if (pos2 == pos[1]){
                            $column = $(this);
                            return false;
                        }
                    });
                    return false;
                }
            });
        } else {
            $column = $boxes.find('.sp-x-lt-col').last();
        }

        $column.append(html);

        var $el = $column.find('.sp-x-box-block').last();
        var entry = initblock($el, data.name, data);

        if (typeof entry.getAreas == 'function') {
            var areas = entry.getAreas();
            var entryData = entry.getData();

            $.each(areas, function (areaIndex, area) {
                if (data.name != area.blockName) {
                    area.block = initblock($el.find(area.container), area.blockName, entryData[area.dataKey]);
                }
            });
        }

        collections.push(entry);
    }

    function initblock($el, name, data) {
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
            var tempA = collections[indexA];
            collections.splice(indexA, 1);
            collections.splice(indexB, 0, tempA);
        }
    }

    function collectionRemove(index) {
        collections.splice(index, 1);
    }

    function collectionCollect(index) {
        var entry = collections[index];

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
        $boxes.find('.sp-x-lt-yy').each(function (index) {
            var colCnt = 0;
            var colEmp = 0;
            $(this).find('.sp-x-lt-col').each(function () {
                colCnt++;

                if ($(this).find('.sp-x-box').length <= 0) {
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
