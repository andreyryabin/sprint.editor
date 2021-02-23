<?php

use Sprint\Editor\AdminEditor;

define("PUBLIC_AJAX_MODE", true);

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

/* @global $APPLICATION CMain */
/* @global $USER CUser */
/* @global $DB CDatabase */

global $APPLICATION;
global $USER;
global $DB;

$request = Bitrix\Main\Context::getCurrent()->getRequest();

$result = [];

if (CModule::IncludeModule('sprint.editor')) {
    $userSettingsName = (string)$request->get('userSettingsName');

    if ($request->get('load')) {
        $packid = $request->get('load');
        $dir = Sprint\Editor\Module::getPacksDir();
        $file = $dir . $packid . '.json';
        if (is_file($file)) {
            $result = file_get_contents($dir . $packid . '.json');
            $result = json_decode($result, true);
        }
    }
    if ($request->get('save')) {
        $json = $request->get('save');
        $packid = md5($json);
        $dir = Sprint\Editor\Module::getPacksDir();
        file_put_contents($dir . $packid . '.json', $json);
        $result['packs'] = AdminEditor::registerPacks(
            [
                'userSettingsName' => $userSettingsName,
            ]
        );
        $result['mess_pack_del'] = GetMessage('SPRINT_EDITOR_pack_del');
    }

    if ($request->get('del')) {
        $packid = $request->get('del');
        $dir = Sprint\Editor\Module::getPacksDir();
        $file = $dir . $packid . '.json';
        if (is_file($file)) {
            unlink($file);
        }
        $result['packs'] = AdminEditor::registerPacks(
            [
                'userSettingsName' => $userSettingsName,
            ]
        );
        $result['mess_pack_del'] = GetMessage('SPRINT_EDITOR_pack_del');
    }

    if ($request->get('show')) {
        $result['packs'] = AdminEditor::registerPacks(
            [
                'userSettingsName' => $userSettingsName,
            ]
        );
        $result['mess_pack_del'] = GetMessage('SPRINT_EDITOR_pack_del');
    }
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($result);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
