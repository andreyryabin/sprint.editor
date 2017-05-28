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

        name = (this._templates[name]) ? name : 'dump';

        if (!this._tplcache[name]) {
            this._tplcache[name] = doT.template(this._templates[name]);
        }

        var tempfn = this._tplcache[name];
        return tempfn(data);
    },

    createInstance: function ($, params) {
        var $container = $('.j-container' + params.uniqid);
        var $resultinput = $('.j-result' + params.uniqid);
        var $selectinput = $('.j-selectblock' + params.uniqid);
        var $addblockinput = $('.j-addblock' + params.uniqid);
        var $blocks = $('.j-blocks' + params.uniqid);

        var $form = $container.closest('form').first();

        var collection = [];

        $('.j-layout-add' + params.uniqid).on('click', function () {
            addLayout($(this).val());
        });

        $('.j-layout-remove' + params.uniqid).on('click', function () {
            var layCnt = $blocks.find('.sp-x-table').length;

            if (layCnt > 1) {
                $blocks.find('.sp-x-table').each(function () {

                    var colCnt = $(this).find('.sp-y-col').length;
                    var colEmp = 0;

                    $(this).find('.sp-y-col').each(function () {
                        if ($(this).is(':empty')) {
                            colEmp++;
                        }
                    });

                    if (colCnt == colEmp && layCnt > 1) {
                        $(this).remove();
                        layCnt--;
                    }
                });
            }


        });

        $('.j-layout-toggle' + params.uniqid).on('click', function () {
            if ($blocks.hasClass('sp-layout-mode')) {
                $blocks.removeClass('sp-layout-mode');
                $('.j-layout-panel' + params.uniqid).removeClass('sp-layout-mode');
            } else {
                $blocks.addClass('sp-layout-mode');
                $('.j-layout-panel' + params.uniqid).addClass('sp-layout-mode');
            }
        });

        addLayout(1);

        for (var prop in params.jsonValue) {
            if (params.jsonValue.hasOwnProperty(prop)) {
                pushblock(params.jsonValue[prop]);
            }
        }

        if (params.enableChange) {
            changeEvents()
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
                post.push(data);
            });


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

        function addLayout(columnCnt) {
            columnCnt = (columnCnt >= 1) ? columnCnt : 1;
            var columns = [];
            for (var index = 1; index <= columnCnt; index++) {
                columns.push({
                    name: index
                })
            }

            var html = sprint_editor.renderTemplate('box-layout', {
                columnCnt: columnCnt,
                columns: columns
            });

            $blocks.append(html);

            var $lastCol = $blocks.find('.sp-x-table').last().find('.sp-y-col');


            var startIndex = 0;
            var stopIndex = 0;


            // $blocks.sortable({
            //     cursor: "move",
            //     items: ".j-box",
            //     handle: ".j-box_handle",
            //     axis: "y",

            // });

            $lastCol.sortable({
                connectWith: ".sp-y-col",
                // axis: "y",
                handle: ".j-box_handle",
                //cancel: ".portlet-toggle",
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

                    swapcollection(startIndex, stopIndex);
                }
            });
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
                    swapcollection(index, nindex);
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
                    swapcollection(index, nindex);
                }
            });

            $addblockinput.on('click', function (e) {
                var name = $selectinput.val();
                pushblock({name: name});
            });

            $container.on('click', '.j-delbox', function (e) {
                e.preventDefault();

                var index = $container.find('.j-delbox').index(this);
                var $box = $(this).closest('.j-box');

                deletecollection(index);
                $box.remove();
            });

            $container.on('click', '.j-minbox', function (e) {
                e.preventDefault();

                var $box = $(this).closest('.j-box');
                var $boxBlock = $box.find('.j-box-block');

                $boxBlock.toggle();

            })
        }

        function pushblock(data) {
            if (!data.name || !sprint_editor.hasBlockParams(data.name)) {
                return false;
            }

            var templateVars = sprint_editor.getBlockParams(data.name);
            templateVars.showSortButtons = params.showSortButtons;
            templateVars.enableChange = params.enableChange;

            var html = sprint_editor.renderTemplate('box', templateVars);

            $blocks.find('.sp-y-col').last().append(html);

            var $el = $blocks.find('.j-box-block').last();
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

            collection.push(entry);
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

        function swapcollection(indexA, indexB) {
            if (indexA !== indexB) {
                var tempA = collection[indexA];
                collection.splice(indexA, 1);
                collection.splice(indexB, 0, tempA);
            }
        }

        function deletecollection(index) {
            collection.splice(index, 1);
        }

    }
};

