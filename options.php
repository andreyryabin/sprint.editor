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
            if (!empty($_REQUEST[$name])) {
                Module::setDbOption($name, 'yes');
            } else {
                Module::setDbOption($name, 'no');
            }
        }
    }

    if (isset($_REQUEST['task_name']) && isset($_REQUEST['task_action'])) {
        UpgradeManager::executeTask(
            $_REQUEST['task_name'],
            $_REQUEST['task_action']
        );
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
                'props' => [],
            ];

        }

        $editorEntities[$entityId]['props'][] = [
            'FIELD_NAME' => $item['FIELD_NAME'],
        ];
    }
}


$editorIblocks = [];
if (CModule::IncludeModule('iblock')) {

    $dbres = CIBlockProperty::GetList(['SORT' => 'ASC'], [
        'USER_TYPE' => 'sprint_editor',
    ]);

    while ($item = $dbres->Fetch()) {
        $iblockId = $item['IBLOCK_ID'];
        if (!isset($editorIblocks[$iblockId])) {
            $iblock = CIBlock::GetList(['SORT' => 'ASC'], [
                'ID' => $iblockId,
            ])->Fetch();
            $iblock['URL'] = "/bitrix/admin/iblock_edit.php?" . http_build_query([
                    'ID' => $iblock['ID'],
                    'admin' => 'Y',
                    'type' => $iblock['IBLOCK_TYPE_ID'],
                    'lang' => LANGUAGE_ID,
                ]);
            $editorIblocks[$iblockId] = [
                'iblock' => $iblock,
                'props' => [],
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
    foreach ($optionsConfig as $name => $aOption):
        $value = Module::getDbOption($name) ?>
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

<?
$taskList = UpgradeManager::getTasks();
?>

<h2><?= GetMessage('SPRINT_EDITOR_TASKS') ?></h2>

<? foreach ($taskList as $aItem): ?>

    <div style="margin-bottom: 20px;">
        <? UpgradeManager::outMessages($aItem['name']) ?>

        <? foreach ($aItem['buttons'] as $button): ?>
            <form method="post" style="display: inline">
                <? if ($aItem['installed'] == 'yes'): ?>
                    <input type="submit" disabled="disabled" value="<?= $button['title'] ?>">
                <? else: ?>
                    <input type="submit" value="<?= $button['title'] ?>">
                <? endif; ?>

                <input type="hidden" name="task_name" value="<?= $aItem['name'] ?>">
                <input type="hidden" name="task_action" value="<?= $button['name'] ?>">
                <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
                <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
                <?= bitrix_sessid_post(); ?>
            </form>
        <? endforeach ?>

        <? if ($aItem['installed'] == 'yes'): ?>
            <strike><?= $aItem['description'] ?></strike>
        <? else: ?>
            <?= nl2br($aItem['description']) ?>
        <? endif; ?>

    </div>

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


<p><?= GetMessage('SPRINT_EDITOR_HELP_TELEGRAM') ?> <br/>
    <a target="_blank" href="https://t-do.ru/sprint_editor">
        https://t-do.ru/sprint_editor
    </a></p>

<p><?= GetMessage('SPRINT_EDITOR_HELP_DONATE') ?> <br/>
    <a target="_blank" href="https://money.yandex.ru/to/410012104240288/500">
        https://money.yandex.ru/to/410012104240288/500
    </a></p>

