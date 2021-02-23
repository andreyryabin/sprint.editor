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
 * @var $layoutsToolbar
 * @var $blocksToolbar
 *
 * @var $enableChange
 * @var $enablePacks
 * @var $jsonUserSettings
 * @var $userSettingsName
 *
 * @var $templates
 */
?>
<div class="sp-x-editor<?= $uniqId ?>">
    <div class="sp-x-editor-lt"></div>
    <? if ($enableChange && ($enablePacks || !empty($layoutsToolbar))) { ?>
        <div class="sp-x-footer">
            <div class="sp-x-buttons sp-x-buttons-lt2">
                <? foreach ($layoutsToolbar as $aGroup) {
                    if (!empty($aGroup['blocks'])) { ?>
                        <div class="sp-x-pp-group">
                            <? foreach ($aGroup['blocks'] as $aBlock) { ?>
                                <span class="sp-x-btn" data-name="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></span>
                            <? } ?>
                        </div>
                    <? }
                } ?>
                <? if ($enablePacks) { ?>
                    <div class="sp-x-packs-loader"></div>
                    <div class="sp-x-pp-group">
                        <span class="sp-x-btn" data-name="save_pack" title="<?= GetMessage('SPRINT_EDITOR_pack_save_title') ?>"><?= GetMessage('SPRINT_EDITOR_pack_save_content') ?></span>
                        <a class="sp-x-btn" href="/bitrix/admin/sprint_editor.php?<?= http_build_query(['lang' => LANGUAGE_ID, 'currentSettingsId' => $userSettingsName]) ?>" title="<?= GetMessage('SPRINT_EDITOR_PACKS_TITLE') ?>" target="_blank"><?= GetMessage('SPRINT_EDITOR_PACKS_PAGE') ?></a>
                    </div>
                <? } ?>
            </div>
        </div>
    <? } ?>
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
        sprint_editor_full($, {
            uniqid: "<?= $uniqId ?>",
            enableChange: <?=$enableChange?>,
            enablePacks: <?=$enablePacks?>,
            userSettingsName: "<?=$userSettingsName?>",
            jsonUserSettings: <?=$jsonUserSettings?>,
        }, {
            jsonValue: <?=$jsonValue?>
        });
    });
</script>
