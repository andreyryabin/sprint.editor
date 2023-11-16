<?php
/**
 * @var $jsonEditorValue
 *
 * @var $jsonBlocksConfigs
 * @var $jsonTemplates
 *
 * @var $uniqId
 * @var $inputName
 * @var $editorName
 *
 * @var $saveEmpty
 * @var $wideMode
 * @var $enableChange
 *
 * @var $firstRun
 * @var $jsonLayoutsToolbar
 * @var $jsonBlocksToolbar
 *
 * @var $jsonUserSettings
 * @var $userSettingsName
 *
 * @var $jsonTemplates
 */
?>
<div class="sp-x-editor<?= $uniqId ?>"></div>
<textarea class="sp-x-result<?= $uniqId ?>" name="<?= $inputName ?>" style="display: none;"></textarea>

<?php if ($firstRun) {
    CModule::IncludeModule('fileman');
    $compParamsLangMess = CComponentParamsManager::GetLangMessages();
    $compParamsLangMess = CUtil::PhpToJSObject($compParamsLangMess, false);

    $sprintEditorLangMess = \Sprint\Editor\Locale::GetLangMessages();
    $sprintEditorLangMess = CUtil::PhpToJSObject($sprintEditorLangMess, false);
    ?>
    <script type="text/javascript">
        BX.message(<?=$compParamsLangMess?>);
        BX.message(<?=$sprintEditorLangMess?>);

        sprint_editor.registerConfigs(<?=$jsonBlocksConfigs?>);
        sprint_editor.registerTemplates(<?=$jsonTemplates?>);
        jQuery(window).focus(function () {
            sprint_editor.fireEvent('window:focus');
        });

    </script>
<?php } ?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        sprint_editor.createInstance($, {
            uniqid: "<?= $uniqId ?>",
            editorName: "<?= $editorName ?>",
            userSettingsName: "<?=$userSettingsName?>",
            enableChange: <?=($enableChange ? 'true' : 'false')?>,
            wideMode: <?=($wideMode ? 'true' : 'false')?>,
            saveEmpty: <?=($saveEmpty ? 'true' : 'false')?>,
            userSettings: <?=$jsonUserSettings?>,
            layoutsToolbar: <?=$jsonLayoutsToolbar?>,
            blocksToolbar: <?=$jsonBlocksToolbar?>,
        }, <?=$jsonEditorValue?>);
    });
</script>
