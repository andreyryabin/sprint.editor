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
                        <optgroup data-type="<?= $aGroup['type'] ?>" label="<?= $aGroup['title'] ?>">
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
                       title="<?= GetMessage('SPRINT_EDITOR_pack_save') ?>"
                       class="sp-x-pack-save adm-btn"
                       value="+"/>

                <input type="button"
                       title="<?= GetMessage('SPRINT_EDITOR_pack_del') ?>"
                       class="sp-x-pack-del adm-btn"
                       value="-"/>
            </div>
        </div>
    <? endif; ?>
</div>

<script type="text/html" id="sp-x-template-pp<?= $uniqId ?>">
    <div class="sp-x-editor-pp">
        <? foreach ($selectValues as $aGroup): if (in_array($aGroup['type'], array('blocks_blocks', 'blocks_my'))): ?>
            <div class="sp-x-editor-pp-group" style="margin-bottom: 10px;" data-type="<?= $aGroup['type'] ?>">
                <div class="sp-x-editor-pp-group-title"><?= $aGroup['title'] ?></div>
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