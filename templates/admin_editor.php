<?php
/**
 * @var $jsonValue
 *
 * @var $jsonParameters
 * @var $jsonTemplates
 *
 * @var $uniqId
 * @var $inputName
 * @var $editorName
 * @var $saveEmpty
 * @var $wideMode
 *
 * @var $firstRun
 * @var $jsonLayoutsToolbar
 * @var $jsonBlocksToolbar
 *
 * @var $enableChange
 * @var $jsonUserSettings
 * @var $userSettingsName
 *
 * @var $templates
 */
?>
<div class="sp-x-editor<?= $uniqId ?>"></div>
<textarea class="sp-x-result<?= $uniqId ?>" name="<?= $inputName ?>" style="display: none;"></textarea>

<?php if ($firstRun): ?><?php
    CModule::IncludeModule('fileman');
    $compParamsLangMess = CComponentParamsManager::GetLangMessages();
    $compParamsLangMess = CUtil::PhpToJSObject($compParamsLangMess, false);

    $sprintEditorLangMess = \Sprint\Editor\Locale::GetLangMessages();
    $sprintEditorLangMess = CUtil::PhpToJSObject($sprintEditorLangMess, false);

    ?>
    <?php foreach ($templates as $templateName => $templateHtml): ?>
        <script type="text/html" id="sp-x-template-<?= $templateName ?>">
            <?= $templateHtml ?>
        </script>
    <?php endforeach; ?>

    <script type="text/javascript">
        BX.message(<?=$compParamsLangMess?>);
        BX.message(<?=$sprintEditorLangMess?>);

        sprint_editor.registerParameters(<?=$jsonParameters?>);

        jQuery(window).focus(function () {
            sprint_editor.fireEvent('window:focus');
        });

    </script>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        sprint_editor_full($, {
            uniqid: "<?= $uniqId ?>",
            editorName: "<?= $editorName ?>",
            enableChange: <?=$enableChange?>,
            wideMode: <?=$wideMode?>,
            saveEmpty: <?=$saveEmpty?>,
            userSettingsName: "<?=$userSettingsName?>",
            jsonUserSettings: <?=$jsonUserSettings?>,
            jsonLayoutsToolbar: <?=$jsonLayoutsToolbar?>,
            jsonBlocksToolbar: <?=$jsonBlocksToolbar?>,
        }, {
            jsonValue: <?=$jsonValue?>
        });
    });
</script>
