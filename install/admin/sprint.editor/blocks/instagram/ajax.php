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
    echo Sprint\Editor\Blocks\Instagram::getHtml(
        [
            'url' => $_REQUEST['url'],
        ]
    );
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
