<?php
/** @global $APPLICATION CMain */

use Sprint\Editor\Exceptions\AdminPageException;
use Sprint\Editor\PackBuilder;

global $APPLICATION;
$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_PACKS_PAGE'));

$request = Bitrix\Main\Context::getCurrent()->getRequest();

$currentUserSettingsName = (string)$request->get('currentUserSettingsName');
$currentPackId = (string)$request->get('currentPackId');

$currentPackTitle = PackBuilder::getPackTitle($currentPackId);
$currentPackJson = PackBuilder::getPackJson($currentPackId);

$currentPackId = $currentPackJson ? $currentPackId : '';

$newPackId = '';
$lastErr = '';

if ($request->isPost() && check_bitrix_sessid()) {
    if ($request->getPost('save_pack')) {
        try {
            $currentPackJson = $request->getPost('pack_content');
            $currentPackTitle = $request->getPost('pack_title');

            if ($currentPackId) {
                PackBuilder::updateBlock(
                    $currentPackId,
                    $currentPackJson,
                    $currentPackTitle,
                    $currentUserSettingsName
                );
            } else {
                $newPackId = $request->getPost('pack_id');

                $currentPackId = PackBuilder::createPack(
                    $newPackId,
                    $currentPackJson,
                    $currentPackTitle,
                    $currentUserSettingsName
                );
            }
            LocalRedirect(
                'sprint_editor.php?' . http_build_query([
                    'lang'                    => LANGUAGE_ID,
                    'currentUserSettingsName' => $currentUserSettingsName,
                    'currentPackId'           => $currentPackId,
                    'showpage'                => $request->get('showpage'),
                ])
            );
        } catch (AdminPageException $e) {
            $lastErr = (new CAdminMessage(
                [
                    "MESSAGE" => $e->getMessage(),
                    'HTML'    => true,
                    'TYPE'    => 'ERROR',
                ]
            ))->Show();
        }
    }

    if ($request->getPost('delete_pack')) {
        PackBuilder::deletePack($currentPackId);
        LocalRedirect(
            'sprint_editor.php?' . http_build_query([
                'lang'                    => LANGUAGE_ID,
                'currentUserSettingsName' => $currentUserSettingsName,
                'showpage'                => $request->get('showpage'),
            ])
        );
    }
}

$userfiles = Sprint\Editor\AdminEditor::getUserSettingsFiles();
$registeredPacks = Sprint\Editor\AdminEditor::registerPacks($currentUserSettingsName);

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
                        <?php foreach ($registeredPacks as $pack) { ?>
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
                    <?= $lastErr ?>
                    <form action="" method="post">
                        <?= bitrix_sessid_post() ?>
                        <div class="sp-x-header">
                            <div class="sp-table sp-table-spacing">
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <strong><?= GetMessage('SPRINT_EDITOR_pack_id') ?></strong>
                                    </div>
                                </div>
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <?php if ($currentPackId) { ?>
                                            <?= $currentPackId ?>
                                        <?php } else { ?>
                                            <input name="pack_id" placeholder="pack_file_name" type="text" value="<?= $newPackId ?>">
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <strong><?= GetMessage('SPRINT_EDITOR_pack_title') ?></strong>
                                    </div>
                                </div>
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <input name="pack_title" placeholder="<?= GetMessage('SPRINT_EDITOR_new_pack') ?>" value="<?= $currentPackTitle ?>" type="text">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?= Sprint\Editor\AdminEditor::init([
                            'uniqId'       => 'uniqId',
                            'value'        => $currentPackJson,
                            'inputName'    => 'pack_content',
                            'defaultValue' => '',
                            'userSettings' => [
                                'SETTINGS_NAME'  => $currentUserSettingsName,
                                'DISABLE_CHANGE' => '',
                                'WIDE_MODE'      => '',
                            ],
                        ]); ?>
                        <div style="background-color: #e3ecee;border: 1px solid #c4ced2;padding: 10px;margin-bottom: 10px">
                            <input class="adm-btn adm-btn-save" name="save_pack" value="<?= GetMessage('SPRINT_EDITOR_pack_save') ?>" type="submit">
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
