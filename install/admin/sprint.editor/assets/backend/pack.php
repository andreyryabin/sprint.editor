<?php
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

if (!check_bitrix_sessid() || !$USER->IsAuthorized()) {
    http_response_code(403);
    die('Forbidden');
}

$request = Bitrix\Main\Context::getCurrent()->getRequest();

$result = [];


if (CModule::IncludeModule('sprint.editor')) {
    $userSettingsName = (string)$request->get('userSettingsName');

    if ($request->get('load')) {
        $packid = preg_replace('#[^A-Za-z0-9_-]#', '', (string)$request->get('load'));

        $packFile = Sprint\Editor\Module::getModuleFile(
            Sprint\Editor\Module::getPacksDir(),
            $packid . '.json'
        );

        if (is_file($packFile)) {
            $result = json_decode(file_get_contents($packFile), true);
        }
    }
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($result);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
