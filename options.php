<?php
$module_id = "sprint.editor";
CModule::IncludeModule($module_id);

global $APPLICATION;
$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
if (!($MODULE_RIGHT >= "R")) {
    $APPLICATION->AuthForm("ACCESS_DENIED");
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && check_bitrix_sessid()) {

    if (isset($_REQUEST['opts_save'])) {
        $optionsConfig = \Sprint\Editor\Module::getOptionsConfig();
        foreach ($optionsConfig as $name => $aOption) {
            if (!empty($_REQUEST[$name])) {
                \Sprint\Editor\Module::setDbOption($name, 'yes');
            } else {
                \Sprint\Editor\Module::setDbOption($name, 'no');
            }
        }
    }

    if (isset($_REQUEST['install_upgrade']) && !empty($_REQUEST['upgrade_name'])) {
        \Sprint\Editor\UpgradeManager::executeUpgrade($_REQUEST['upgrade_name']);
    }

    if (isset($_REQUEST['install_task']) && !empty($_REQUEST['task_name'])) {
        \Sprint\Editor\UpgradeManager::executeTask($_REQUEST['task_name']);
    }

}

$editorEntities = array();
if (\CModule::IncludeModule('main')) {
    $dbres = \CUserTypeEntity::GetList(array(), array('USER_TYPE_ID' => 'sprint_editor'));
    while ($item = $dbres->Fetch()) {

        $entityId = $item['ENTITY_ID'];
        if (!isset($editorEntities[$entityId])) {
            $editorEntities[$entityId] = array(
                'entity' => $item,
                'props' => array()
            );

        }

        $editorEntities[$entityId]['props'][] = array(
            'FIELD_NAME' => $item['FIELD_NAME']
        );
    }
}


$editorIblocks = array();
if (\CModule::IncludeModule('iblock')) {

    $dbres = \CIBlockProperty::GetList(array('SORT' => 'ASC'), array(
        'USER_TYPE' => 'sprint_editor'
    ));

    while ($item = $dbres->Fetch()) {
        $iblockId = $item['IBLOCK_ID'];
        if (!isset($editorIblocks[$iblockId])) {
            $iblock = \CIBlock::GetList(array('SORT' => 'ASC'), array(
                'ID' => $iblockId
            ))->Fetch();
            $iblock['URL'] = "/bitrix/admin/iblock_edit.php?" . http_build_query(array(
                    'ID' => $iblock['ID'],
                    'admin' => 'Y',
                    'type' => $iblock['IBLOCK_TYPE_ID'],
                    'lang' => LANGUAGE_ID
                ));
            $editorIblocks[$iblockId] = array(
                'iblock' => $iblock,
                'props' => array()
            );
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
    $optionsConfig = \Sprint\Editor\Module::getOptionsConfig();
    foreach ($optionsConfig as $name => $aOption):
        $value = \Sprint\Editor\Module::getDbOption($name) ?>
        <label>
            <input <? if ($value == 'yes'): ?>checked="checked"<? endif ?>
                   type="checkbox"
                   name="<?= $name ?>"
                   value="<?= $aOption['DEFAULT'] ?>">
            <?= $aOption['TITLE'] ?>
        </label><br/>
    <? endforeach; ?>
    <br/>
    <input class="adm-btn-green" type="submit" name="opts_save" value="<?= GetMessage('SPRINT_EDITOR_BTN_SAVE') ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<br/>
<br/>

<h2><?= GetMessage('SPRINT_EDITOR_TASKS') ?></h2>
<?
$taskList = \Sprint\Editor\UpgradeManager::getTasks();
foreach ($taskList as $aItem):?>
    <form method="post">
        <? \Sprint\Editor\UpgradeManager::outMessages($aItem['name']) ?>
        <input type="submit" name="install_task" value="<?= GetMessage('SPRINT_EDITOR_BTN_EXECUTE') ?>"> -
        <?= $aItem['description'] ?>
        <input type="hidden" name="task_name" value="<?= $aItem['name'] ?>">
        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
        <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
        <?= bitrix_sessid_post(); ?>
    </form>
    <br/>
<? endforeach; ?>

<h2><?= GetMessage('SPRINT_EDITOR_UPGRADES') ?></h2>
<?
$upgradeList = \Sprint\Editor\UpgradeManager::getUpgrades();
foreach ($upgradeList as $aItem):?>
    <form method="post">
        <? \Sprint\Editor\UpgradeManager::outMessages($aItem['name']) ?>

        <input type="submit" name="install_upgrade" value="<?= GetMessage('SPRINT_EDITOR_BTN_EXECUTE') ?>"> -
        <? if ($aItem['installed'] == 'yes'): ?>
            <strike><?= $aItem['description'] ?></strike>
        <? else: ?>
            <?= $aItem['description'] ?>
        <? endif; ?>

        <input type="hidden" name="upgrade_name" value="<?= $aItem['name'] ?>">
        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
        <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
        <?= bitrix_sessid_post(); ?>

    </form>
    <br/>
<? endforeach; ?>


<? if (!empty($editorIblocks)): ?>
    <h2><?= GetMessage('SPRINT_EDITOR_USED_IBLOCKS') ?></h2>

    <table class='c-result'>
        <tr>
            <th>id</th>
            <th>code</th>
            <th>url</th>
            <th>props</th>
        </tr>
        <? foreach ($editorIblocks as $item): ?>
            <tr>
                <td>
                    <?= $item['iblock']['ID'] ?>
                </td>
                <td>
                    <?= $item['iblock']['CODE'] ?>
                </td>
                <td>
                    <a target="_blank" href="<?= $item['iblock']['URL'] ?>">
                        <?= $item['iblock']['NAME'] ?>
                    </a>
                </td>
                <td>
                    <? foreach ($item['props'] as $prop): ?>
                        <?= $prop['CODE'] ?><br/>
                    <? endforeach; ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>

    <br/>
<? endif; ?>



<? if (!empty($editorEntities)): ?>
    <h2><?= GetMessage('SPRINT_EDITOR_USED_ENTITIES') ?></h2>

    <table class='c-result'>
        <tr>
            <th>entity</th>
            <th>fields</th>
        </tr>
        <? foreach ($editorEntities as $item): ?>
            <tr>
                <td>
                    <?= $item['entity']['ENTITY_ID'] ?>
                </td>
                <td>
                    <? foreach ($item['props'] as $prop): ?>
                        <?= $prop['FIELD_NAME'] ?><br/>
                    <? endforeach; ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
    <br/>
<? endif; ?>


<h2><?= GetMessage('SPRINT_EDITOR_HELP') ?></h2>
<p><?= GetMessage('SPRINT_EDITOR_HELP_WIKI') ?> <br/>
    <a target="_blank" href="https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home">
        https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home
    </a></p>

<p><?= GetMessage('SPRINT_EDITOR_HELP_MARKETPLACE') ?> <br/>
    <a target="_blank" href="http://marketplace.1c-bitrix.ru/solutions/sprint.editor/">
        http://marketplace.1c-bitrix.ru/solutions/sprint.editor/
    </a></p>
