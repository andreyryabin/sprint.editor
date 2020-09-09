<?php

use Sprint\Editor\UploadHandler;

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
    if (!empty($_REQUEST['url'])) {
        $handler = new UploadHandler(
            [
                'bitrix_resize' => [
                    'width'  => 98,
                    'height' => 55,
                    'exact'  => 1,
                ],
            ], false
        );

        $handler->saveResource($_REQUEST['url']);
    }
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
