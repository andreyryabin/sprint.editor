<?php
$module_id = "sprint.editor";
CModule::IncludeModule($module_id);

use Sprint\Editor\Module;
use Sprint\Editor\UpgradeManager;

global $APPLICATION;
if ($APPLICATION->GetGroupRight($module_id) == 'D') {
    $APPLICATION->AuthForm("ACCESS_DENIED");
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && check_bitrix_sessid()) {
    if (isset($_REQUEST['opts_save'])) {
        $optionsConfig = Module::getOptionsConfig();
        foreach ($optionsConfig as $name => $aOption) {
            if ($aOption['TYPE'] == 'checkbox') {
                if (!empty($_REQUEST[$name])) {
                    Module::setDbOption($name, 'yes');
                } else {
                    Module::setDbOption($name, 'no');
                }
            } elseif ($aOption['TYPE'] == 'text') {
                Module::setDbOption($name, isset($_REQUEST[$name]) ? strval($_REQUEST[$name]) : '');
            }
        }
    }

    if (isset($_REQUEST['task_name']) && isset($_REQUEST['task_action'])) {
        foreach ($_REQUEST['task_action'] as $taskAction => $taskTitle) {
            UpgradeManager::executeTask(
                $_REQUEST['task_name'],
                $taskAction
            );
        }
    }
}

$editorEntities = [];
if (CModule::IncludeModule('main')) {
    $dbres = CUserTypeEntity::GetList([], ['USER_TYPE_ID' => 'sprint_editor']);
    while ($item = $dbres->Fetch()) {
        $entityId = $item['ENTITY_ID'];
        if (!isset($editorEntities[$entityId])) {
            $editorEntities[$entityId] = [
                'entity' => $item,
                'props'  => [],
            ];
        }

        $editorEntities[$entityId]['props'][] = [
            'FIELD_NAME' => $item['FIELD_NAME'],
        ];
    }
}

$editorIblocks = [];
if (CModule::IncludeModule('iblock')) {
    $dbres = CIBlockProperty::GetList(
        ['SORT' => 'ASC'], [
            'USER_TYPE' => 'sprint_editor',
        ]
    );

    while ($item = $dbres->Fetch()) {
        $iblockId = $item['IBLOCK_ID'];
        if (!isset($editorIblocks[$iblockId])) {
            $iblock = CIBlock::GetList(
                ['SORT' => 'ASC'], [
                    'ID' => $iblockId,
                ]
            )->Fetch();
            $iblock['URL'] = "/bitrix/admin/iblock_edit.php?" . http_build_query(
                    [
                        'ID'    => $iblock['ID'],
                        'admin' => 'Y',
                        'type'  => $iblock['IBLOCK_TYPE_ID'],
                        'lang'  => LANGUAGE_ID,
                    ]
                );
            $iblock['URL2'] = "/bitrix/admin/iblock_list_admin.php?" . http_build_query(
                    [
                        'IBLOCK_ID'            => $iblock['ID'],
                        'type'                 => $iblock['IBLOCK_TYPE_ID'],
                        'lang'                 => LANGUAGE_ID,
                        'find_section_section' => '0',
                        'SECTION_ID'           => '0',
                        'apply_filter'         => 'Y',
                    ]
                );
            $editorIblocks[$iblockId] = [
                'iblock' => $iblock,
                'props'  => [],
            ];
        }

        $editorIblocks[$iblockId]['props'][] = $item;
    }
}

?>
<style type="text/css">
    .c-result {
        border-collapse: collapse;
    }

    .c-result th, .c-result td {
        vertical-align: top;
        padding: 5px;
        border: 1px solid #cecece;
    }
</style>

<form method="post">
    <?
    $optionsConfig = Module::getOptionsConfig();
    foreach ($optionsConfig as $name => $aOption) {
        $value = Module::getDbOption($name) ?>
        <div style="margin-bottom: 10px">
            <? if ($aOption['TYPE'] == 'checkbox') { ?>
                <label>
                    <input <? if ($value == 'yes'){ ?>checked="checked"<? } ?>
                           type="checkbox"
                           name="<?= $name ?>"
                           value="<?= $aOption['DEFAULT'] ?>">
                    <?= $aOption['TITLE'] ?>
                </label>
            <? } elseif ($aOption['TYPE'] == 'text') { ?>
                <label>
                    <input type="text" name="<?= $name ?>" value="<?= $value ?>"/>
                    <?= $aOption['TITLE'] ?>
                </label>
            <? } ?>
        </div>
    <? } ?>
    <br/>
    <input class="adm-btn-green" type="submit" name="opts_save" value="<?= GetMessage('SPRINT_EDITOR_BTN_SAVE') ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<br/>
<br/>

<?
$taskList = UpgradeManager::getTasks();
?>

<h2><?= GetMessage('SPRINT_EDITOR_TASKS') ?></h2>

<? foreach ($taskList as $aItem) { ?>
    <? if ($aItem['installed'] != 'yes') { ?>
        <div style="margin-bottom: 20px;">
            <form method="post">
                <? UpgradeManager::outMessages($aItem['name']) ?>
                <div>
                    <? if ($aItem['installed'] == 'yes') { ?>
                        <strike><?= $aItem['description'] ?></strike>
                    <? } else { ?>
                        <?= nl2br($aItem['description']) ?>
                    <? } ?>
                </div>
                <? foreach ($aItem['buttons'] as $button) { ?>
                    <input type="submit" name="task_action[<?= $button['name'] ?>]" value="<?= $button['title'] ?>">
                <? } ?>
                <input type="hidden" name="task_name" value="<?= $aItem['name'] ?>">
                <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
                <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
                <?= bitrix_sessid_post(); ?>
            </form>
        </div>
    <? } ?>
<? } ?>

<? if (!empty($editorIblocks)) { ?>
    <h2><?= GetMessage('SPRINT_EDITOR_USED_IBLOCKS') ?></h2>

    <table class='c-result'>
        <? foreach ($editorIblocks as $item) { ?>
            <tr>
                <td>
                    [<?= $item['iblock']['ID'] ?>] [<?= $item['iblock']['CODE'] ?>] <?= $item['iblock']['NAME'] ?>
                </td>
                <td>
                    <? foreach ($item['props'] as $prop) { ?>
                        [<?= $prop['ID'] ?>] [<?= $prop['CODE'] ?>] <?= $prop['NAME'] ?><br/>
                    <? } ?>
                </td>
                <td>
                    <a target="_blank" href="<?= $item['iblock']['URL2'] ?>"><?=GetMessage('SPRINT_EDITOR_LONG_TEXT_IBLOCK_LINK_ELEM')?></a>
                    <br/>
                    <a target="_blank" href="<?= $item['iblock']['URL'] ?>"><?=GetMessage('SPRINT_EDITOR_LONG_TEXT_IBLOCK_LINK_SETT')?></a>
                </td>
            </tr>
        <? } ?>
    </table>
    <br/>
<? } ?>



<? if (!empty($editorEntities)) { ?>
    <h2><?= GetMessage('SPRINT_EDITOR_USED_ENTITIES') ?></h2>

    <table class='c-result'>
        <tr>
            <th>entity</th>
            <th>fields</th>
        </tr>
        <? foreach ($editorEntities as $item) { ?>
            <tr>
                <td>
                    <?= $item['entity']['ENTITY_ID'] ?>
                </td>
                <td>
                    <? foreach ($item['props'] as $prop) { ?>
                        <?= $prop['FIELD_NAME'] ?><br/>
                    <? } ?>
                </td>
            </tr>
        <? } ?>
    </table>
    <br/>
<? } ?>


<h2><?= GetMessage('SPRINT_EDITOR_HELP') ?></h2>
<p><?= GetMessage('SPRINT_EDITOR_HELP_WIKI') ?> <br/>
    <a target="_blank" href="https://github.com/andreyryabin/sprint.editor/wiki">
        https://github.com/andreyryabin/sprint.editor/wiki
    </a></p>

<p><?= GetMessage('SPRINT_EDITOR_HELP_MARKETPLACE') ?> <br/>
    <a target="_blank" href="http://marketplace.1c-bitrix.ru/solutions/sprint.editor/">
        http://marketplace.1c-bitrix.ru/solutions/sprint.editor/
    </a></p>


<p><?= GetMessage('SPRINT_EDITOR_HELP_TELEGRAM') ?> <br/>
    <a target="_blank" href="https://t.me/sprint_editor">
        https://t.me/sprint_editor
    </a></p>

<p><?= GetMessage('SPRINT_EDITOR_HELP_DONATE') ?> <br/>
    <a target="_blank" href="https://yoomoney.ru/to/410012104240288">
        https://yoomoney.ru/to/410012104240288
    </a></p>

