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
 * @var $wideMode
 *
 * @var $firstRun
 * @var $layoutsToolbar
 * @var $blocksToolbar
 *
 * @var $enableChange
 * @var $jsonUserSettings
 * @var $userSettingsName
 *
 * @var $templates
 */
?>
<div class="sp-x-editor<?= $uniqId ?>">
    <div class="sp-x-editor-lt"></div>
    <?php if ($enableChange && !empty($layoutsToolbar)) { ?>
        <div class="sp-x-footer">
            <div class="sp-x-buttons sp-x-buttons-lt2">
                <?php foreach ($layoutsToolbar as $aGroup) {
                    if (!empty($aGroup['blocks'])) { ?>
                        <div class="sp-x-pp-group">
                            <?php foreach ($aGroup['blocks'] as $aBlock) { ?>
                                <span class="sp-x-btn" data-name="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></span>
                            <?php } ?>
                        </div>
                    <?php }
                } ?>
            </div>
        </div>
    <?php } ?>
</div>
<script type="text/html" id="sp-x-template-pp-blocks<?= $uniqId ?>">
    <div class="sp-x-pp-blocks">
        <?php foreach ($blocksToolbar as $aGroup) { ?>
            <div class="sp-x-pp-group">
                <div class="sp-x-pp-group-title"><?= $aGroup['title'] ?></div>
                <?php foreach ($aGroup['blocks'] as $aBlock) { ?>
                    <span class="sp-x-btn" data-name="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></span>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</script>

<textarea class="sp-x-result<?= $uniqId ?>" name="<?= $inputName ?>" style="display: none;"></textarea>

<?php if ($firstRun): ?><?php
    \CModule::IncludeModule('fileman');
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
            userSettingsName: "<?=$userSettingsName?>",
            jsonUserSettings: <?=$jsonUserSettings?>,
        }, {
            jsonValue: <?=$jsonValue?>
        });
    });
</script>
