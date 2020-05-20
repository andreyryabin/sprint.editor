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

if (CModule::IncludeModule('sprint.editor')) {
    if (!empty($_REQUEST['items']) && is_array($_REQUEST['items'])) {
        foreach ($_REQUEST['items'] as $item) {
            if (isset($item['file']) && isset($item['file']['ID'])) {
                CFile::Delete($item['file']['ID']);
            }
        }
    }
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
