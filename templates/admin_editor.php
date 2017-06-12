<?php
/**
 * @var $rawValue
 *
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
 * @var $showSortButtons
 */
?>
<div class="sp-x-editor<?= $uniqId ?>">
    <textarea style="display: none;" class="sp-x-result" name="<?= $inputName ?>"></textarea>
    <div class="sp-x-boxes"></div>
<? if ($enableChange): ?>
    <? if (!empty($selectValues)): ?>
        <select class="sp-x-box-select" style="width: 250px;">
            <? foreach ($selectValues as $aGroup): ?>
                <optgroup label="<?= $aGroup['title'] ?>">
                    <? foreach ($aGroup['blocks'] as $aBlock): ?>
                        <option value="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></option>
                    <? endforeach; ?>
                </optgroup>
            <? endforeach; ?>
        </select>
        <input value="<?= GetMessage('SPRINT_EDITOR_BTN_ADD') ?>"
               class="sp-x-box-add adm-btn-green"
               type="button"/>
    <? else: ?>
        <?= GetMessage('SPRINT_EDITOR_SELECT_EMPTY') ?>
    <? endif; ?>
    <div class="sp-x-buttons" style="float:right;display: none">
    <input title="<?= GetMessage('SPRINT_EDITOR_layout_toggle') ?>"
           type="button"
           class="sp-x-layout-toggle"
           value="#"/>
    <input title="<?= GetMessage('SPRINT_EDITOR_layout_remove') ?>"
           type="button"
           class="sp-x-layout-del"
           value="x"/>
    </div>
<? endif; ?>
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
<? endif;?>
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