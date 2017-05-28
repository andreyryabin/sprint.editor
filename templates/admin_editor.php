<?php
/**
 * @var $rawValue
 *
 * @var $jsonValue
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
 * @var $showSortButtons
 */
?>
<div class="j-container<?= $uniqId ?>">
    <div class="j-blocks<?= $uniqId ?>"></div>

    <? if ($enableChange): ?>
        <? if (!empty($selectValues)): ?>
            <select class="j-selectblock<?= $uniqId ?>" style="width: 250px;">
                <? foreach ($selectValues as $aGroup): ?>
                    <optgroup label="<?= $aGroup['title'] ?>">
                        <? foreach ($aGroup['blocks'] as $aBlock): ?>
                            <option value="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></option>
                        <? endforeach; ?>
                    </optgroup>
                <? endforeach; ?>
            </select>
            <input class="j-addblock<?= $uniqId ?> adm-btn-green" type="button"
                   value="<?= GetMessage('SPRINT_EDITOR_BTN_ADD') ?>"/>
        <? else: ?>
            <?= GetMessage('SPRINT_EDITOR_SELECT_EMPTY') ?>
        <? endif; ?>
        <a href="/bitrix/admin/settings.php?lang=ru&mid=sprint.editor&mid_menu=1"
           class="sp-options-link"
           title="<?= GetMessage('SPRINT_EDITOR_OPTIONS_LINK') ?>"
           target="_blank">(?)</a>
    <? endif; ?>

    <? if ($enableChange): ?>
        <input type="button" class="j-layout-toggle<?= $uniqId ?>" value="#">

        <div class="j-layout-panel<?= $uniqId ?> sp-layout-panel">
            <input type="button" class="j-layout-add<?= $uniqId ?>" value="1">
            <input type="button" class="j-layout-add<?= $uniqId ?>" value="2">
            <input type="button" class="j-layout-add<?= $uniqId ?>" value="3">
            <input type="button" class="j-layout-add<?= $uniqId ?>" value="4">
            <input type="button" class="j-layout-remove<?= $uniqId ?>" value="x">
        </div>
    <? endif; ?>

    <textarea style="display: none;" class="j-result<?= $uniqId ?>" name="<?= $inputName ?>"></textarea>
</div>

<? if ($firstRun): ?><?php
    \CModule::IncludeModule('fileman');
    $compParamsLangMess = CComponentParamsManager::GetLangMessages();
    $compParamsLangMess = CUtil::PhpToJSObject($compParamsLangMess, false);
    ?>
    <script type="text/javascript">
        BX.message(<?=$compParamsLangMess?>);
        sprint_editor.registerTemplates(<?=$jsonTemplates?>);
        sprint_editor.registerParameters(<?=$jsonParameters?>);
    </script>
<? endif ?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        sprint_editor_create($, {
            uniqid: "<?= $uniqId ?>",
            enableChange: <?=$enableChange?>,
            showSortButtons: <?=$showSortButtons?>,
            jsonValue: <?=$jsonValue?>
        });
    });
</script>