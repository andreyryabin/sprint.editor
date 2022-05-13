sprint_editor.registerBlock('snippet', function ($, $el, data, settings, currentEditorParams) {
    data = $.extend({
        file: '',
    }, data);

    currentEditorParams = currentEditorParams || {};

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.file = $el.find('.sp-select').val();
        return data;
    };

    this.afterRender = function () {
        $el.on('change', '.sp-select', function () {
            renderSelect($(this).val());
        });

        renderSelect(data.file);
    };

    let renderSelect = function (file) {
        let snippets = sprint_editor.getSnippets(currentEditorParams)
        snippets = Array.isArray(snippets) ? snippets : [];

        let description = '';
        $.each(snippets, function (index, snippet) {
            if (snippet.file === file) {
                description = snippet.description;
            }
        });

        $el.html(
            sprint_editor.renderTemplate('snippet-select', {
                snippets: snippets,
                file: file,
                description: description,
            })
        );
    };

});
