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
 * @var $selectValues
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
                <div class="sp-x-lt-col sp-active"></div>
            </div>
            <div class="sp-x-buttons sp-x-buttons-ed">
                <?if ($enableChange):?>
                <span class="sp-x-btn sp-x-btn-green sp-x-pp-blocks-open"><?= GetMessage('SPRINT_EDITOR_add_block') ?></span>
                <span class="sp-x-btn sp-x-lastblock"><?= GetMessage('SPRINT_EDITOR_add') ?></span>
                <?endif;?>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="sp-x-template-pp-blocks<?= $uniqId ?>">
    <div class="sp-x-pp-blocks">
        <? foreach ($selectValues as $groupType => $aGroup): if (in_array($groupType, array('blocks_blocks', 'blocks_my'))): ?>
            <div class="sp-x-pp-group">
                <div class="sp-x-pp-group-title"><?= $aGroup['title'] ?></div>
                <? foreach ($aGroup['blocks'] as $aBlock): ?>
                    <span class="sp-x-btn" data-name="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></span>
                <? endforeach; ?>
            </div>
        <? endif;endforeach ?>
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
            sprint_editor.fireEvent('focus');
        });

    </script>
<? endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        sprint_editor_simple($, {
            uniqid: "<?= $uniqId ?>",
            enableChange: <?=$enableChange?>,
            jsonUserSettings:<?=$jsonUserSettings?>,
            jsonValue: <?=$jsonValue?>
        });
    });
</script>