var sprint_editor_public = {

    create: function ($, params) {
        var $editor = $('.sp-p-editor' + params.uniqid);

        var $box = $editor.find('.sp-p-editor-bx');

        if (!params.jsonValue) {
            params.jsonValue = {};
        }

        if (!params.jsonValue.blocks) {
            params.jsonValue.blocks = [];
        }

        if (!params.jsonValue.layouts) {
            params.jsonValue.layouts = [];
        }

        if (!params.jsonUserSettings) {
            params.jsonUserSettings = {};
        }


        $editor.on('click','.sp-p-box',function(){
            var uid = $(this).data('uid');

            $.each(params.jsonValue.blocks, function (index, block) {
                if (block.uid == uid){
                    blockAdd(block);
                }


            });


        })


        function blockAdd(blockData) {
            if (!blockData || !blockData.name || !blockData.uid) {
                return false;
            }

            if (!sprint_editor.hasBlockParams(blockData.name)) {
                return false;
            }

            var uid = blockData.uid;

            var blockSettings = sprint_editor.getBlockSettings(blockData.name,params);
            var html = sprint_editor.renderBlock(blockData, blockSettings, uid,params);

            $box.empty().html(html);

            var $el = $box.find('.sp-x-box-block').last();
            var entry = sprint_editor.initblock($, $el, blockData.name, blockData, blockSettings);


            sprint_editor.initblockAreas($, $el, entry);
            sprint_editor._entries[uid] = entry;
        }
    }
};



