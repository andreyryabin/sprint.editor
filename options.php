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

    if (isset($_REQUEST['enable_blocks'])){
        $blockGroups = \Sprint\Editor\AdminEditor::getBlockGroups();
        foreach ($blockGroups as $aGroup){
            $optname = 'enable_blocks_' . $aGroup['name'];
            if (!empty($_REQUEST[$optname])){
                \Sprint\Editor\Module::setDbOption($optname, 'yes');
            } else {
                \Sprint\Editor\Module::setDbOption($optname, 'no');
            }
        }
    }

    if (isset($_REQUEST['install_upgrade']) && !empty($_REQUEST['upgrade_name'])){
        \Sprint\Editor\UpgradeManager::executeUpgrade($_REQUEST['upgrade_name']);
    }

    if (isset($_REQUEST['install_task']) && !empty($_REQUEST['task_name'])){
        \Sprint\Editor\UpgradeManager::executeTask($_REQUEST['task_name']);
    }

}

?>
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
    <input class="adm-btn-green" type="submit" name="opts_save" value="<?=GetMessage('SPRINT_EDITOR_BTN_SAVE')?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<br/>
<br/>

<h2><?=GetMessage('SPRINT_EDITOR_ENABLE_BLOCKS')?></h2>
<form method="post">
<?
    $blockGroups = \Sprint\Editor\AdminEditor::getBlockGroups();
    foreach ($blockGroups as $aGroup):?>
        <label>
            <input <? if ($aGroup['enable'] == 'yes'): ?>checked="checked"<? endif ?>
                   type="checkbox"
                   name="enable_blocks_<?= $aGroup['name'] ?>"
                   value="yes">
            <?= \Sprint\Editor\Locale::convertToWin1251IfNeed($aGroup['title'])?>
        </label><br/>
    <?endforeach;?>
    <br/>
    <input class="adm-btn-green" type="submit" name="enable_blocks" value="<?=GetMessage('SPRINT_EDITOR_BTN_SAVE')?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<br/>
<br/>

<h2><?=GetMessage('SPRINT_EDITOR_TASKS')?></h2>
<?
$taskList = \Sprint\Editor\UpgradeManager::getTasks();
foreach ($taskList as $aItem):?>
<form method="post">
    <?  \Sprint\Editor\UpgradeManager::outMessages($aItem['name']) ?>
    <input type="submit" name="install_task" value="<?=GetMessage('SPRINT_EDITOR_BTN_EXECUTE')?>"> -
    <?=$aItem['description']?>
    <input type="hidden" name="task_name" value="<?=$aItem['name']?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<br/>
<?endforeach;?>

<h2><?=GetMessage('SPRINT_EDITOR_UPGRADES')?></h2>
<?
$upgradeList = \Sprint\Editor\UpgradeManager::getUpgrades();
foreach ($upgradeList as $aItem):?>
<form method="post">
    <?  \Sprint\Editor\UpgradeManager::outMessages($aItem['name']) ?>

    <input type="submit" name="install_upgrade" value="<?=GetMessage('SPRINT_EDITOR_BTN_EXECUTE')?>"> -
    <?if ($aItem['installed'] == 'yes'):?>
        <strike><?=$aItem['description']?></strike>
    <?else:?>
        <?=$aItem['description']?>
    <?endif;?>

    <input type="hidden" name="upgrade_name" value="<?=$aItem['name']?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
    <?= bitrix_sessid_post(); ?>

</form>
<br/>
<?endforeach;?>


<h2><?=GetMessage('SPRINT_EDITOR_HELP')?></h2>
<p><?=GetMessage('SPRINT_EDITOR_HELP_WIKI')?> <br/>
<a target="_blank" href="https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home">
    https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home
</a></p>

<p><?=GetMessage('SPRINT_EDITOR_HELP_TRACKER')?> <br/>
<a target="_blank" href="https://bitbucket.org/andrey_ryabin/sprint.editor/issues/new">
    https://bitbucket.org/andrey_ryabin/sprint.editor/issues/new
</a></p>

<p><?=GetMessage('SPRINT_EDITOR_HELP_MARKETPLACE')?> <br/>
<a target="_blank" href="http://marketplace.1c-bitrix.ru/solutions/sprint.editor/">
    http://marketplace.1c-bitrix.ru/solutions/sprint.editor/
</a></p>
