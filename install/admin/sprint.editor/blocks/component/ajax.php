<?php

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

if (\CModule::IncludeModule('sprint.editor')) {
    header('Content-type: application/json; charset=utf-8');

    \CModule::IncludeModule('fileman');

    $components = \CHTMLEditor::GetComponents(array(), false, array());
    $sitetemplates = \CHTMLEditor::GetSiteTemplates();

    $result = array(
        'components' => $components['items'],
        'sitetemplates' => $sitetemplates
    );

    echo json_encode(\Sprint\Editor\Locale::convertToUtf8IfNeed($result));
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");