<?php
/**
 * @var $jsonEditorParams
 * @var $jsonEditorValue
 * @var $jsonBlocksConfigs
 * @var $jsonTemplates
 * @var $uniqId
 * @var $inputName
 */

CModule::IncludeModule('fileman');
$compParamsLangMess = CComponentParamsManager::GetLangMessages();
$compParamsLangMess = CUtil::PhpToJSObject($compParamsLangMess, false);

$sprintEditorLangMess = \Sprint\Editor\Locale::GetLangMessages();
$sprintEditorLangMess = CUtil::PhpToJSObject($sprintEditorLangMess, false);
?>
<div class="sp-x-editor<?= $uniqId ?>"><?= GetMessage('SPRINT_EDITOR_init_error') ?></div>
<textarea class="sp-x-result<?= $uniqId ?>" name="<?= $inputName ?>" style="display: none;"><?= $jsonEditorValue ?></textarea>

<script type="text/javascript">
    if (!window.sprint_editor_inited) {
        window.sprint_editor_inited = true;
        BX.message(<?=$compParamsLangMess?>);
        BX.message(<?=$sprintEditorLangMess?>);

        sprint_editor.registerConfigs(<?=$jsonBlocksConfigs?>);
        sprint_editor.registerTemplates(<?=$jsonTemplates?>);
        jQuery(window).focus(function () {
            sprint_editor.fireEvent('window:focus');
        });
    }
</script>

<script type="text/javascript">
    if (document.readyState === 'loading') {
        // DOM ещё загружается, ждём события
        document.addEventListener('DOMContentLoaded', () => {
            sprint_editor.createInstance(jQuery, <?=$jsonEditorParams?>, <?=$jsonEditorValue?>);
        });
    } else {
        // DOM готов!
        sprint_editor.createInstance(jQuery, <?=$jsonEditorParams?>, <?=$jsonEditorValue?>);
    }
</script>
