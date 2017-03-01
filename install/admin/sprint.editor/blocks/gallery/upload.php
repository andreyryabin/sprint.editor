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


if (\CModule::IncludeModule('sprint.editor')){
    $handler = new \Sprint\Editor\UploadHandler(array(
        'bitrix_resize' => array(
            'width' => 100,
            'height' => 100,
            'exact' => 1
        )
    ));
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");