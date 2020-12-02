<?php
/**
 * @var $jsonValue
 *
 * @var $jsonParameters
 * @var $jsonTemplates
 *
 * @var $uniqId
 * @var $inputName
 * @var $formName
 *
 * @var $firstRun
 * @var $blocksToolbar
 *
 * @var $enableChange
 * @var $jsonUserSettings
 *
 * @var $templates
 */
?>
<div class="sp-x-editor<?= $uniqId ?> sp-x-editor-simple">
    <div class="sp-x-editor-lt">
        <div class="sp-x-lt">
            <div class="sp-x-lt-row">
                <div class="sp-x-col sp-x-active"></div>
            </div>
            <? if ($enableChange) { ?>
                <div class="sp-x-buttons sp-x-buttons-lt2">
                    <span class="sp-x-btn sp-x-btn-green sp-x-pp-blocks-open"><?= GetMessage('SPRINT_EDITOR_add_block') ?></span>
                    <span class="sp-x-btn sp-x-lastblock"><?= GetMessage('SPRINT_EDITOR_add') ?></span>
                </div>
            <? } ?>
        </div>
    </div>
</div>
<script type="text/html" id="sp-x-template-pp-blocks<?= $uniqId ?>">
    <div class="sp-x-pp-blocks">
        <? foreach ($blocksToolbar as $aGroup) { ?>
            <div class="sp-x-pp-group">
                <div class="sp-x-pp-group-title"><?= $aGroup['title'] ?></div>
                <? foreach ($aGroup['blocks'] as $aBlock) { ?>
                    <span class="sp-x-btn" data-name="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></span>
                <? } ?>
            </div>
        <? } ?>
    </div>
</script>

<textarea class="sp-x-result<?= $uniqId ?>" name="<?= $inputName ?>" style="display: none;"></textarea>

<? if ($firstRun): ?><?php
    \CModule::IncludeModule('fileman');
    $compParamsLangMess = CComponentParamsManager::GetLangMessages();
    $compParamsLangMess = CUtil::PhpToJSObject($compParamsLangMess, false);

    $sprintEditorLangMess = \Sprint\Editor\Locale::GetLangMessages();
    $sprintEditorLangMess = CUtil::PhpToJSObject($sprintEditorLangMess, false);

    ?>
    <? foreach ($templates as $templateName => $templateHtml): ?>
        <script type="text/html" id="sp-x-template-<?= $templateName ?>">
            <?= $templateHtml ?>
        </script>
    <? endforeach; ?>

    <script type="text/javascript">
        BX.message(<?=$compParamsLangMess?>);
        BX.message(<?=$sprintEditorLangMess?>);

        sprint_editor.registerParameters(<?=$jsonParameters?>);

        jQuery(window).focus(function () {
            sprint_editor.fireEvent('window:focus');
        });

    </script>
<? endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        sprint_editor_simple($, {
            uniqid: "<?= $uniqId ?>",
            enableChange: <?=$enableChange?>,
            jsonUserSettings: <?=$jsonUserSettings?>,
        }, {
            jsonValue: <?=$jsonValue?>
        });
    });
</script>
