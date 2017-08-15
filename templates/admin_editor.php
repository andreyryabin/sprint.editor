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
 * @var $jsonUserSettings
 */
?>
<div class="sp-x-editor<?= $uniqId ?>">
    <div class="sp-x-boxes"></div>

    <div class="sp-table">
        <div class="sp-row">
            <div class="sp-col">
                <? if (!empty($selectValues)): ?>
                    <? if ($enableChange): ?>
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
                        <input value="<?= GetMessage('SPRINT_EDITOR_BTN_PASTE') ?>"
                               data-title="<?= GetMessage('SPRINT_EDITOR_BTN_PASTE') ?>"
                               class="sp-x-box-paste"
                               type="button"/>
                    <? endif; ?>
                <? else: ?>
                    <?= GetMessage('SPRINT_EDITOR_SELECT_EMPTY') ?>
                <? endif; ?>
            </div>
            <div class="sp-col" style="text-align: right">
                <input title="<?= GetMessage('SPRINT_EDITOR_layout_toggle') ?>"
                       type="button"
                       class="sp-x-layout-toggle"
                       value="#"/>
                <input title="<?= GetMessage('SPRINT_EDITOR_layout_remove') ?>"
                       type="button"
                       class="sp-x-layout-remove"
                       value="x"/>
            </div>
        </div>
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
            jsonUserSettings:<?=$jsonUserSettings?>,
            jsonValue: <?=$jsonValue?>
        });
    });
</script>