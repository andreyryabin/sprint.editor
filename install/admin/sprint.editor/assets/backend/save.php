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

    $json = $_REQUEST['pack'];

    $uniq = md5($json);

    $dir = Sprint\Editor\Module::getPacksDir();

    file_put_contents($dir. $uniq . '.json', $json);

}


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");