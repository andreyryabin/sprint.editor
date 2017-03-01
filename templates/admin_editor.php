<?php
/**
 * @var $jsonValue
 * @var $jsonParameters
 * @var $jsonTemplates
 * @var $jsonOptions
 *
 * @var $uniqId
 * @var $inputName
 * @var $formName
 *
 * @var $firstRun
 * @var $selectValues

 */
?><div class="j-container<?= $uniqId ?>">
    <div class="j-blocks<?= $uniqId ?>"></div>

    <?if (!empty($selectValues)):?>
    <select class="j-selectblock<?= $uniqId ?>" style="width: 250px;">
        <? foreach ($selectValues as $aGroup): ?>
            <optgroup label="<?=$aGroup['title']?>">
            <? foreach ($aGroup['blocks'] as $aBlock):?>
                <option value="<?= $aBlock['name'] ?>"><?= $aBlock['title'] ?></option>
            <? endforeach; ?>
            </optgroup>
        <? endforeach; ?>
    </select>
    <input class="j-addblock<?= $uniqId ?> adm-btn-green" type="button" value="<?=GetMessage('SPRINT_EDITOR_BTN_ADD')?>">
    <?else:?>
        <?=GetMessage('SPRINT_EDITOR_SELECT_EMPTY')?>
    <?endif;?>

    <a style="float:right;text-decoration: none" title="<?=GetMessage('SPRINT_EDITOR_OPTIONS_LINK')?>" target="_blank" href="/bitrix/admin/settings.php?lang=ru&mid=sprint.editor&mid_menu=1">(?)</a>

    <textarea style="display: none;" class="j-result<?= $uniqId ?>" name="<?= $inputName ?>"></textarea>
</div>
<?if ($firstRun):?><?
?><script type="text/javascript">
    sprint_editor.registerOptions(<?=$jsonOptions?>);
    sprint_editor.registerTemplates(<?=$jsonTemplates?>);
    sprint_editor.registerParameters(<?=$jsonParameters?>);
</script>
<?endif?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        sprint_editor.createInstance($, {
            uniqid: "<?= $uniqId ?>",
            jsonValue: <?=$jsonValue?>
        });
    });
</script>