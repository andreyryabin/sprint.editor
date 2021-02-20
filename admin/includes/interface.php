<?php

$request = Bitrix\Main\Context::getCurrent()->getRequest();
$packsDir = Sprint\Editor\Module::getPacksDir();

$currentSettingsId = (string)$request->get('currentSettingsId');
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
        $currentPackId = $request->getPost('currentPackId');
        $packContentJson = $request->getPost('pack_content');
        $packContent = json_decode($packContentJson, true);
        $packContent['packname'] = (string)$request->getPost('pack_title');
        $packContentJson = json_encode($packContent);

        $currentPackId = ($currentPackId) ? $currentPackId : md5($packContentJson);

        file_put_contents($packsDir . $currentPackId . '.json', $packContentJson);

        $query = $request->getQueryList()->toArray();
        $query['currentPackId'] = $currentPackId;
        LocalRedirect('sprint_editor.php?' . http_build_query($query));
    }
}

$userfiles = Sprint\Editor\AdminEditor::getUserSettingsFiles();
$packs = Sprint\Editor\AdminEditor::registerPacks();

$packContentJson = '';
$currentPackName = '';
if ($currentPackId) {
    if (is_file($packsDir . $currentPackId . '.json')) {
        $packContentJson = file_get_contents($packsDir . $currentPackId . '.json');
        $packContent = json_decode($packContentJson, true);
        $currentPackName = $packContent['packname'] ?? '';
    }
}

$editorParams = [
    'uniqId'       => 'uniqId',
    'value'        => $packContentJson,
    'inputName'    => 'pack_content',
    'defaultValue' => '',
    'userSettings' => [
        'SETTINGS_NAME'  => $currentSettingsId,
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
                    <form action="" method="get">
                        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
                        <div style="margin-bottom: 10px">
                            <?= GetMessage('SPRINT_EDITOR_field_pack') ?><br/>
                            <select style="width: 250px" name="currentPackId">
                                <option value=""><?= GetMessage('SPRINT_EDITOR_new_pack') ?></option>
                                <? foreach ($packs as $pack) { ?>
                                    <option <?= ($currentPackId == $pack['name'] ? 'selected="selected"' : '') ?> value="<?= $pack['name'] ?>"><?= $pack['title'] ?></option>
                                <? } ?>
                            </select>

                        </div>
                        <div style="margin-bottom: 10px">
                            <?= GetMessage('SPRINT_EDITOR_pack_user_settings') ?> <br/>
                            <select style="width: 250px" name="currentSettingsId">
                                <? foreach ($userfiles as $settingsId => $settingsName) { ?>
                                    <option <?= ($currentSettingsId == $settingsId ? 'selected="selected"' : '') ?> value="<?= $settingsId ?>"><?= $settingsName ?></option>
                                <? } ?>
                            </select>
                        </div>
                        <div style="margin-bottom: 10px">
                            <input type="submit" value="<?= GetMessage('SPRINT_EDITOR_pack_open') ?>">
                        </div>
                    </form>
                </td>
                <td class="adm-detail-valign-top" style="width: 60%">
                    <form action="" method="post">
                        <input type="hidden" name="currentPackId" value="<?= $currentPackId ?>">
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
