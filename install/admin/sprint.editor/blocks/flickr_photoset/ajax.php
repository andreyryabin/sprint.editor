<?php

use Sprint\Editor\Blocks\FlickrPhotoset;

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

$res = [];

if (CModule::IncludeModule('sprint.editor')) {
    if (isset($_REQUEST['photoset_id'])) {
        $res = FlickrPhotoset::getInfoWithPreviews(htmlspecialchars($_REQUEST['photoset_id']));
    } elseif (isset($_REQUEST['page'])) {
        $res = FlickrPhotoset::getPhotosets(intval($_REQUEST['page']));
    }
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($res);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
