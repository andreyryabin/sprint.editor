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
 * @var $showSortButtons
 * @var $showCopyButtons
 * @var $jsonUserSettings
 */
?>
<div class="sp-x-editor<?= $uniqId ?>">
    <div class="sp-x-editor-lt"></div>
    <? if ($enableChange): ?>
    <div class="sp-x-editor-bt">
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
        <input type="button"
               title="<?= GetMessage('SPRINT_EDITOR_layout_toggle') ?>"
               class="sp-x-layout-toggle adm-btn"
               value="#"/>


        <div style="float: right">
        <input type="button"
               class="sp-x-pack-save adm-btn"
               value="+"/>

        <input type="button"
               class="sp-x-pack-del adm-btn"
               value="-"/>
        </div>

        <? endif; ?>
    </div>
</div>

<textarea class="sp-x-result<?= $uniqId ?>" name="<?= $inputName ?>" style="display: none;"></textarea>

<? if ($firstRun): ?><?php
    \CModule::IncludeModule('fileman');
    $compParamsLangMess = CComponentParamsManager::GetLangMessages();
    $compParamsLangMess = CUtil::PhpToJSObject($compParamsLangMess, false);
    ?>
    <script type="text/javascript">
        BX.message(<?=$compParamsLangMess?>);
        sprint_editor.registerTemplates(<?=$jsonTemplates?>);
        sprint_editor.registerParameters(<?=$jsonParameters?>);

        jQuery(window).focus(function () {
            sprint_editor.fireEvent('focus');
        });

    </script>
<? endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        sprint_editor.create($, {
            uniqid: "<?= $uniqId ?>",
            enableChange: <?=$enableChange?>,
            showSortButtons: <?=$showSortButtons?>,
            showCopyButtons: <?=$showCopyButtons?>,
            jsonUserSettings:<?=$jsonUserSettings?>,
            jsonValue: <?=$jsonValue?>
        });
    });
</script>