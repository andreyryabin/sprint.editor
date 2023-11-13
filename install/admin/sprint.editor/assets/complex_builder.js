function complex_builder($, currentEditorParams, currentEditorValue) {

    let $editor = $('#' + currentEditorParams.uniqid + '_editor');
    let $inputresult = $('#' + currentEditorParams.uniqid + '_result');

    let $form = $editor.closest('form').first();

    let register_blocks = registerBlocks();

    let $container = $(createContainer());
    $editor.append($container);

    let $layoutToolbar = $(createLayoutToolbar());
    $editor.append($layoutToolbar);

    let $blockToolbar = $(createBlockToolbar());
    $editor.append($blockToolbar);

    let $blockTitle = $form.find('input[name=block_title]');
    let $blockSort = $form.find('input[name=block_sort]');

    if (currentEditorValue.hasOwnProperty('title')) {
        $blockTitle.val(currentEditorValue['title']);
    }

    if (currentEditorValue.hasOwnProperty('sort')) {
        $blockSort.val(currentEditorValue['sort']);
    }

    if (currentEditorValue.hasOwnProperty('layouts')) {
        $.each(currentEditorValue['layouts'], function (index, layout) {
            addLayout(layout);
        });
    } else {
        addLayoutDefault('layout_1');
    }

    $layoutToolbar.on('click', '.sp-x-block', function () {
        var name = $(this).data('name');
        addLayoutDefault(name)
    });

    $blockToolbar.on('click', '.sp-x-block', function () {
        var $lastCol = $editor.find('.sp-x-lt').last().find('.sp-x-sortable').first();

        $lastCol.append($(this).clone())
    });

    $editor.on('click', '.sp-x-del', function () {
        $(this).closest('.sp-x-lt').remove();
    });

    $editor.on('keypress', 'input', function (e) {
        let keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    $editor.on("paste", '.sp-x-caption', function (e) {
        e.preventDefault();

        var text = e.originalEvent.clipboardData.getData("text/plain");
        text = text.replace(/(\r\n|\n|\r)/gm, "");
        document.execCommand("insertText", false, text);
    });

    $editor.on("keypress", '.sp-x-caption', function (e) {
        if (e.keyCode === 13) {
            return false;
        }
    });

    $form.on('submit', function () {
        let resultString = saveToString();

        $editor.find('input,textarea,select').removeAttr('name');
        $inputresult.val(resultString);
    });

    function createContainer() {
        return $('<div class="sp-x-container"></div>');
    }

    function createLayoutToolbar() {
        let html = '<div class="sp-x-toolbar">';

        html += '<div class="sp-x-pp-group">';
        html += '<div class="sp-x-pp-group-title">' + 'Шаблоны' + '</div>';

        html += '<span class="sp-x-block" data-name="' + 'layout_1' + '">' + '1 колонка' + '</span>';
        html += '<span class="sp-x-block" data-name="' + 'layout_2' + '">' + '2 колонки' + '</span>';
        html += '<span class="sp-x-block" data-name="' + 'layout_3' + '">' + '3 колонки' + '</span>';
        html += '<span class="sp-x-block" data-name="' + 'layout_4' + '">' + '4 колонки' + '</span>';

        html += '</div>';


        html += '</div>';
        return html;
    }

    function createBlockToolbar() {
        let html = '<div class="sp-x-toolbar">';
        $.each(currentEditorParams['toolbar'], function (gindex, group) {
            html += '<div class="sp-x-pp-group">';
            html += '<div class="sp-x-pp-group-title">' + group['title'] + '</div>';
            $.each(group['blocks'], function (bindex, block) {
                html += '<span class="sp-x-block" data-name="' + block['name'] + '">' + block['title'] + '</span>';
            });
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    function addLayout(layout) {

        let title = layout['title'] || '';

        let html = '';
        html += '<div class="sp-x-lt">';
        html += '<div class="sp-x-lt-header">';
        html += '<span class="sp-x-del">x</span>';
        html += '<span class="sp-x-caption" contenteditable="true">' + title + '</span>';
        html += '</div>';
        html += '<div class="sp-table">';
        html += '<div class="sp-row">';
        $.each(layout['columns'], function (index, column) {
            html += '<div class="sp-col sp-x-sortable">';
            $.each(column['blocks'], function (index, blockName) {
                let blockTitle = register_blocks[blockName] || blockName;
                html += '<span class="sp-x-block" data-name="' + blockName + '">' + blockTitle + '</span>';
            });
            html += '</div>';
        });
        html += '</div></div></div>';

        var $layot = $(html)

        $container.append($layot);

        $container.find('.sp-x-sortable').sortable({
            items: ".sp-x-block",
            connectWith: '.sp-x-sortable',
        });
    }

    function registerBlocks() {
        let res = {}
        $.each(currentEditorParams['toolbar'], function (gindex, group) {
            $.each(group['blocks'], function (bindex, block) {
                res[block['name']] = block['title'];
            });
        });
        return res;
    }

    function addLayoutDefault(name) {
        let columnCnt = 1;
        if (name.indexOf('layout_') === 0) {
            columnCnt = name.substring(7);
        }

        let columns = [];
        for (let index = 0; index < columnCnt; index++) {
            columns.push({
                "css": "",
                "blocks": []
            })
        }

        addLayout({
            "css": "",
            "columns": columns
        });

    }

    function saveToString() {
        let layouts = [];
        $editor.find('.sp-x-lt').each(function () {
            let columns = [];

            let caption = $(this).find('.sp-x-caption').text();

            $(this).find('.sp-x-sortable').each(function () {
                let blocks = [];

                $(this).children('.sp-x-block').each(function () {
                    blocks.push($(this).data('name'));
                });

                columns.push({
                    'blocks': blocks
                })
            });

            layouts.push({
                'title': caption,
                'columns': columns
            })
        });

        return JSON.stringify({
            'title': $blockTitle.val(),
            'sort': $blockSort.val(),
            'layouts': layouts
        });
    }
}
