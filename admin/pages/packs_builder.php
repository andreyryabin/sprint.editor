<?php
/** @global $APPLICATION CMain */
global $APPLICATION;
$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_PACKS_PAGE'));

$request = Bitrix\Main\Context::getCurrent()->getRequest();
$packsDir = Sprint\Editor\Module::getPacksDir();

$currentUserSettingsName = (string)$request->get('currentUserSettingsName');
$currentPackId = (string)$request->get('currentPackId');

if ($request->isPost() && check_bitrix_sessid()) {
    if ($request->getPost('delete_pack')) {
        $file = $packsDir . $currentPackId . '.json';
        if ($currentPackId && is_file($file)) {
            unlink($file);

            LocalRedirect(
                'sprint_editor.php?' . http_build_query([
                    'lang'                    => LANGUAGE_ID,
                    'currentUserSettingsName' => $currentUserSettingsName,
                    'showpage'                => $request->get('showpage'),
                ])
            );
        }
    }
    if ($request->getPost('save_pack')) {
        $packContentJson = $request->getPost('pack_content');
        $packContent = json_decode($packContentJson, true);
        $packContent = is_array($packContent) ? $packContent : [];
        $packContent = array_merge([
            'version' => 2,
            'blocks'  => [],
            'layouts' => [],
        ], $packContent, [
            'packname'         => (string)$request->getPost('pack_title'),
            'userSettingsName' => $currentUserSettingsName,
        ]);

        $packContentJson = json_encode($packContent, JSON_UNESCAPED_UNICODE);

        $currentPackId = ($currentPackId) ? $currentPackId : md5($packContentJson);

        file_put_contents($packsDir . $currentPackId . '.json', $packContentJson);

        LocalRedirect(
            'sprint_editor.php?' . http_build_query([
                'lang'                    => LANGUAGE_ID,
                'currentUserSettingsName' => $currentUserSettingsName,
                'currentPackId'           => $currentPackId,
                'showpage'                => $request->get('showpage'),
            ])
        );
    }
}

$userfiles = Sprint\Editor\AdminEditor::getUserSettingsFiles();
$packs = Sprint\Editor\AdminEditor::registerPacks($currentUserSettingsName);

$packContentJson = '';
$currentPackName = '';
if ($currentPackId) {
    if (is_file($packsDir . $currentPackId . '.json')) {
        $packContentJson = file_get_contents($packsDir . $currentPackId . '.json');
        $packContent = json_decode($packContentJson, true);
        $currentPackName = (string)($packContent['packname'] ?? '');
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
        'WIDE_MODE'      => '',
    ],
];

?>
<div class="adm-detail-content" style="padding: 0">
    <div class="adm-detail-content-item-block">
        <table class="adm-detail-content-table edit-table">
            <tbody>
            <tr class="">
                <td class="adm-detail-valign-top" style="width: 40%">
                    <div><?= GetMessage('SPRINT_EDITOR_pack_user_settings') ?></div>
                    <div class="sp-side-left">
                        <?php foreach ($userfiles as $settingsName => $settingsTitle) { ?>
                            <a class="sp-link <?= ($currentUserSettingsName == $settingsName ? 'sp-link-active' : '') ?>"
                               href="<?= 'sprint_editor.php?' . http_build_query(
                                   [
                                       'lang'                    => LANGUAGE_ID,
                                       'currentUserSettingsName' => $settingsName,
                                       'showpage'                => $request->get('showpage'),
                                   ]
                               ) ?>"><?= $settingsTitle ?></a>
                        <?php } ?>
                    </div>
                    <div style="margin-top: 10px;"><?= GetMessage('SPRINT_EDITOR_field_packs') ?></div>
                    <div class="sp-side-left">
                        <a class="sp-link sp-link-new <?= ($currentPackId == '' ? 'sp-link-active' : '') ?>"
                           href="<?= 'sprint_editor.php?' . http_build_query(
                               [
                                   'lang'                    => LANGUAGE_ID,
                                   'currentPackId'           => '',
                                   'currentUserSettingsName' => $currentUserSettingsName,
                                   'showpage'                => $request->get('showpage'),
                               ]
                           ) ?>">Новый макет</a>
                    </div>
                    <div class="sp-side-left">
                        <?php foreach ($packs as $pack) { ?>
                            <a class="sp-link <?= ($currentPackId == $pack['name'] ? 'sp-link-active' : '') ?>"
                               href="<?= 'sprint_editor.php?' . http_build_query(
                                   [
                                       'lang'                    => LANGUAGE_ID,
                                       'currentPackId'           => $pack['name'],
                                       'currentUserSettingsName' => $currentUserSettingsName,
                                       'showpage'                => $request->get('showpage'),
                                   ]
                               ) ?>"><?= $pack['title'] ?></a>
                        <?php } ?>
                    </div>
                </td>
                <td class="adm-detail-valign-top" style="width: 60%">
                    <form action="" method="post">
                        <?= bitrix_sessid_post() ?>
                        <div style="background-color: #e3ecee;border: 1px solid #c4ced2;padding: 10px;margin-bottom: 10px">
                            <?= GetMessage('SPRINT_EDITOR_pack_name') ?><br/>
                            <input style="width: 78%" name="pack_title" value="<?= $currentPackName ?>" type="text">
                        </div>
                        <?= Sprint\Editor\AdminEditor::init($editorParams); ?>
                        <div style="background-color: #e3ecee;border: 1px solid #c4ced2;padding: 10px;margin-bottom: 10px">
                            <input class="adm-btn" name="save_pack" value="<?= GetMessage('SPRINT_EDITOR_pack_save') ?>" type="submit">
                            <?php if ($currentPackId) { ?>
                                <input class="adm-btn" name="delete_pack" value="<?= GetMessage('SPRINT_EDITOR_pack_delete') ?>" type="submit">
                            <?php } ?>
                        </div>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
