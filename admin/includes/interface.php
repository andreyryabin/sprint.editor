<?php

$request = Bitrix\Main\Context::getCurrent()->getRequest();
$packsDir = Sprint\Editor\Module::getPacksDir();

$currentUserSettingsName = (string)$request->get('currentUserSettingsName');
$currentPackId = (string)$request->get('currentPackId');

if ($request->isPost()) {
    if ($request->getPost('delete_pack')) {
        $file = $packsDir . $currentPackId . '.json';
        if ($currentPackId && is_file($file)) {
            unlink($file);

            $query = $request->getQueryList()->toArray();
            $query['currentPackId'] = '';
            LocalRedirect('sprint_editor.php?' . http_build_query($query));
        }
    }
    if ($request->getPost('save_pack')) {
        $packContentJson = $request->getPost('pack_content');
        $packContent = json_decode($packContentJson, true);
        $packContent['packname'] = (string)$request->getPost('pack_title');
        $packContentJson = json_encode($packContent, JSON_UNESCAPED_UNICODE);

        $currentPackId = ($currentPackId) ? $currentPackId : md5($packContentJson);

        file_put_contents($packsDir . $currentPackId . '.json', $packContentJson);

        $query = $request->getQueryList()->toArray();
        $query['currentPackId'] = $currentPackId;
        LocalRedirect('sprint_editor.php?' . http_build_query($query));
    }
}

$userfiles = Sprint\Editor\AdminEditor::getUserSettingsFiles();
$packs = Sprint\Editor\AdminEditor::registerPacks(
    [
        'userSettingsName' => $currentUserSettingsName,
    ]
);

$packContentJson = '';
$currentPackName = '';
if ($currentPackId) {
    if (is_file($packsDir . $currentPackId . '.json')) {
        $packContentJson = file_get_contents($packsDir . $currentPackId . '.json');
        $packContent = json_decode($packContentJson, true);
        $currentPackName = (string)(isset($packContent['packname']) ? $packContent['packname'] : '');
    }
}

$editorParams = [
    'uniqId'       => 'uniqId',
    'value'        => $packContentJson,
    'inputName'    => 'pack_content',
    'defaultValue' => '',
    'userSettings' => [
        'SETTINGS_NAME'  => $currentUserSettingsName,
        'DISABLE_CHANGE' => '',
        'DISABLE_PACKS'  => 'Y',
    ],
];

?>
<div class="adm-detail-content" style="padding: 0">
    <div class="adm-detail-content-item-block">
        <table class="adm-detail-content-table edit-table">
            <tbody>
            <tr class="">
                <td class="adm-detail-valign-top" style="width: 40%">
                    <div style="margin-bottom: 10px">
                        <?= GetMessage('SPRINT_EDITOR_pack_user_settings') ?><br/>
                        <? foreach ($userfiles as $settingsName => $settingsTitle) { ?>
                            <div style="margin-bottom: 10px;">
                                <a class="adm-btn <?= ($currentUserSettingsName == $settingsName ? 'adm-btn-active' : '') ?>"
                                   href="sprint_editor.php?<?= http_build_query(
                                       [
                                           'lang'                    => LANGUAGE_ID,
                                           'currentUserSettingsName' => $settingsName,
                                       ]
                                   ) ?>"><?= $settingsTitle ?></a>
                            </div>
                        <? } ?>
                    </div>
                    <div style="margin-bottom: 10px">
                        <?= GetMessage('SPRINT_EDITOR_field_packs') ?><br/>
                        <div style="margin-bottom: 10px;">
                            <a class="adm-btn adm-btn-save <?= ($currentPackId == '' ? 'adm-btn-save-active' : '') ?>"
                               href="sprint_editor.php?<?= http_build_query(
                                   [
                                       'lang'                    => LANGUAGE_ID,
                                       'currentPackId'           => '',
                                       'currentUserSettingsName' => $currentUserSettingsName,
                                   ]
                               ) ?>">Новый макет</a>
                        </div>
                        <? foreach ($packs as $pack) { ?>
                            <div style="margin-bottom: 10px;">
                                <a class="adm-btn <?= ($currentPackId == $pack['name'] ? 'adm-btn-active' : '') ?>"
                                   href="sprint_editor.php?<?= http_build_query(
                                       [
                                           'lang'                    => LANGUAGE_ID,
                                           'currentPackId'           => $pack['name'],
                                           'currentUserSettingsName' => $currentUserSettingsName,
                                       ]
                                   ) ?>"><?= $pack['title'] ?></a>
                            </div>
                        <? } ?>
                    </div>
                </td>
                <td class="adm-detail-valign-top" style="width: 60%">
                    <form action="" method="post">
                        <div style="background-color: #e3ecee;border: 1px solid #c4ced2;padding: 10px;margin-bottom: 10px">
                            <?= GetMessage('SPRINT_EDITOR_pack_name') ?><br/>
                            <input style="width: 78%" name="pack_title" value="<?= $currentPackName ?>" type="text">
                        </div>
                        <?= Sprint\Editor\AdminEditor::init($editorParams); ?>
                        <div style="background-color: #e3ecee;border: 1px solid #c4ced2;padding: 10px;margin-bottom: 10px">
                            <input class="adm-btn" name="save_pack" value="<?= GetMessage('SPRINT_EDITOR_pack_save') ?>" type="submit">
                            <? if ($currentPackId) { ?>
                                <input class="adm-btn" name="delete_pack" value="<?= GetMessage('SPRINT_EDITOR_pack_delete') ?>" type="submit">
                            <? } ?>
                        </div>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
