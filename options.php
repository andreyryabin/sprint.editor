<?php
$module_id = "sprint.editor";
CModule::IncludeModule($module_id);

use Sprint\Editor\Module;
use Sprint\Editor\UpgradeManager;

global $APPLICATION;
if ($APPLICATION->GetGroupRight($module_id) == 'D') {
    $APPLICATION->AuthForm("ACCESS_DENIED");
}

$APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/admin_pages.css?3');

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
            } elseif (in_array($aOption['TYPE'], ['text', 'select'])) {
                Module::setDbOption($name, (string)($_REQUEST[$name] ?? ''));
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
<style>
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
    <?php
    $optionsConfig = Module::getOptionsConfig();
    foreach ($optionsConfig as $name => $aOption) {
        $value = Module::getDbOption($name) ?>
        <div style="margin-bottom: 10px">
            <?php if ($aOption['TYPE'] == 'checkbox') { ?>
                <label>
                    <input <?php if ($value == 'yes'){ ?>checked="checked"<?php } ?>
                           type="checkbox"
                           name="<?= $name ?>"
                           value="<?= $aOption['DEFAULT'] ?>">
                    <?= $aOption['TITLE'] ?>
                </label>
            <?php } elseif ($aOption['TYPE'] == 'select') { ?>
                <label title="<?= $value ?>">
                    <?= $aOption['TITLE'] ?>
                    <select name="<?= $name ?>">
                        <?php foreach ($aOption['ITEMS'] as $sVal => $sTitle) { ?>
                            <option <?php if ($value == $sVal){ ?>selected="selected"<?php } ?> value="<?= $sVal ?>">
                                <?= $sTitle ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
            <?php } elseif ($aOption['TYPE'] == 'text') { ?>
                <label>
                    <input type="text" name="<?= $name ?>" value="<?= $value ?>"/>
                    <?= $aOption['TITLE'] ?>
                </label>
            <?php } ?>
        </div>
    <?php } ?>
    <br/>
    <input class="adm-btn-green" type="submit" name="opts_save" value="<?= GetMessage('SPRINT_EDITOR_BTN_SAVE') ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<br/>
<br/>

<?php
$taskList = UpgradeManager::getTasks();
?>

<h2><?= GetMessage('SPRINT_EDITOR_TASKS') ?></h2>

<?php foreach ($taskList as $aItem) { ?>
    <?php if ($aItem['installed'] != 'yes') { ?>
        <div style="margin-bottom: 20px;">
            <form method="post">
                <?php UpgradeManager::outMessages($aItem['name']) ?>
                <div><?= nl2br($aItem['description']) ?></div>
                <?php foreach ($aItem['buttons'] as $button) { ?>
                    <input type="submit" name="task_action[<?= $button['name'] ?>]" value="<?= $button['title'] ?>">
                <?php } ?>
                <input type="hidden" name="task_name" value="<?= $aItem['name'] ?>">
                <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
                <input type="hidden" name="mid" value="<?= urlencode($module_id) ?>">
                <?= bitrix_sessid_post(); ?>
            </form>
        </div>
    <?php } ?>
<?php } ?>

<?php if (!empty($editorIblocks)) { ?>
    <h2><?= GetMessage('SPRINT_EDITOR_USED_IBLOCKS') ?></h2>

    <table class='c-result'>
        <?php foreach ($editorIblocks as $item) { ?>
            <tr>
                <td>
                    [<?= $item['iblock']['ID'] ?>] [<?= $item['iblock']['CODE'] ?>] <?= $item['iblock']['NAME'] ?>
                </td>
                <td>
                    <?php foreach ($item['props'] as $prop) { ?>
                        [<?= $prop['ID'] ?>] [<?= $prop['CODE'] ?>] <?= $prop['NAME'] ?><br/>
                    <?php } ?>
                </td>
                <td>
                    <a target="_blank" href="<?= $item['iblock']['URL2'] ?>"><?= GetMessage('SPRINT_EDITOR_LONG_TEXT_IBLOCK_LINK_ELEM') ?></a>
                    <br/>
                    <a target="_blank" href="<?= $item['iblock']['URL'] ?>"><?= GetMessage('SPRINT_EDITOR_LONG_TEXT_IBLOCK_LINK_SETT') ?></a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <br/>
<?php } ?>


<?php if (!empty($editorEntities)) { ?>
    <h2><?= GetMessage('SPRINT_EDITOR_USED_ENTITIES') ?></h2>

    <table class='c-result'>
        <tr>
            <th>entity</th>
            <th>fields</th>
        </tr>
        <?php foreach ($editorEntities as $item) { ?>
            <tr>
                <td>
                    <?= $item['entity']['ENTITY_ID'] ?>
                </td>
                <td>
                    <?php foreach ($item['props'] as $prop) { ?>
                        <?= $prop['FIELD_NAME'] ?><br/>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
    <br/>
<?php } ?>

<?php
include __DIR__ . '/admin/includes/help.php';
?>
