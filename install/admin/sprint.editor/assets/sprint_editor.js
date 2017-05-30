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
        var params = this.getBlockParams(blockName);
        return '/bitrix/admin/sprint.editor/' + params.group.name + '/' + params.name;
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
    var $container = $('.j-container' + params.uniqid);
    var $resultinput = $('.j-result' + params.uniqid);
    var $selectinput = $('.j-selectblock' + params.uniqid);
    var $addblockinput = $('.j-addblock' + params.uniqid);
    var $blocks = $('.j-blocks' + params.uniqid);

    var $form = $container.closest('form').first();

    var collection = [];
    var lastLayoutType = 0;

    $('.j-layout-remove' + params.uniqid).on('click', function () {
        layoutRemoveEmpty();
    });

    $('.j-layout-toggle' + params.uniqid).on('click', function () {
        if ($blocks.hasClass('sp-layout-mode')) {
            $blocks.removeClass('sp-layout-mode');
        } else {
            $blocks.addClass('sp-layout-mode');
        }
    });

    if (params.enableChange) {
        changeEvents();
    }


    $form.on('submit', function (e) {
        var post = [];
        $.each(collection, function (index, entry) {
            var data = entry.collectData();
            if (typeof entry.getAreas == 'function') {
                var areas = entry.getAreas();
                $.each(areas, function (areaIndex, area) {
                    data[area.dataKey] = area.block.collectData()
                })
            }

            var $block = $blocks.find('.j-box').eq(index);
            var coldata = $block.closest('.sp-y-col').data();

            data.lt_type = coldata.type;
            data.lt_col = coldata.index;

            post.push(data);
        });


        // e.preventDefault();

        var resultString = '';
        if (post.length > 0) {
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
        $resultinput.val(resultString);
    });

    for (var prop in params.jsonValue) {
        if (params.jsonValue.hasOwnProperty(prop)) {
            blockPush(params.jsonValue[prop]);
        }
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
        if ($lastCol.length){
            lastLayoutType = $lastCol.data('type');
        } else {
            lastLayoutType = 0;
        }
        
    }

    function layoutAdd(layoutType, newLayout) {

        if (!newLayout){
            if (lastLayoutType == layoutType){
                return false;
            }
        }

        lastLayoutType = layoutType;

        layoutType = (layoutType >= 1) ? layoutType : 1;
        var columns = [];
        for (var index = 1; index <= layoutType; index++) {
            columns.push({
                index: index,
                type: layoutType
            });
        }

        var html = sprint_editor.renderTemplate('box-layout', {
            columns: columns
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

    function changeEvents() {
        $container.on('click', '.j-upbox', function (e) {
            e.preventDefault();
            var index = $container.find('.j-upbox').index(this);
            var block = $(this).closest('.j-box');

            var nblock = block.prev();
            if (nblock.length > 0) {
                var nindex = nblock.index();

                block.insertBefore(nblock);
                collectionSwap(index, nindex);
            }
        });

        $container.on('click', '.j-dnbox', function (e) {
            e.preventDefault();
            var index = $container.find('.j-dnbox').index(this);
            var block = $(this).closest('.j-box');

            var nblock = block.next();
            if (nblock.length > 0) {
                var nindex = nblock.index();

                block.insertAfter(nblock);
                collectionSwap(index, nindex);
            }
        });

        $addblockinput.on('click', function (e) {
            var name = $selectinput.val();
            if (name.indexOf('layout_') === 0){
                name = name.substr(7);
                layoutAdd(name, 1);
            } else {
                blockPush({name: name});
            }
        });

        $container.on('click', '.j-delbox', function (e) {
            e.preventDefault();

            var index = $container.find('.j-delbox').index(this);
            var $box = $(this).closest('.j-box');

            collectionRemove(index);
            $box.remove();
        });

    }



    function blockPush(data) {
        if (!data.name || !sprint_editor.hasBlockParams(data.name)) {
            return false;
        }

        var templateVars = sprint_editor.getBlockParams(data.name);
        templateVars.showSortButtons = params.showSortButtons;
        templateVars.enableChange = params.enableChange;

        var html = sprint_editor.renderTemplate('box', templateVars);

        if (!data.lt_type ){
            if (lastLayoutType > 0){
                data.lt_type = lastLayoutType;
            } else {
                data.lt_type = 1;
            }

        }

        if (!data.lt_col){
            if (lastLayoutType > 0){
                data.lt_col = lastLayoutType;
            } else {
                data.lt_col = 1;
            }
        }

        layoutAdd(data.lt_type, 0);

        var $layout = $blocks.find('.sp-x-table').last();
        var $column = $layout.find('.sp-y-col').eq(data.lt_col - 1);

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

        collection.push(entry);
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
            var tempA = collection[indexA];
            collection.splice(indexA, 1);
            collection.splice(indexB, 0, tempA);
        }
    }

    function collectionRemove(index) {
        collection.splice(index, 1);
    }

}
