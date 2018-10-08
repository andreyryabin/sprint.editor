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


$result = [];

if (\CModule::IncludeModule('sprint.editor')){

    if (isset($_REQUEST['save'])) {
        $json = $_REQUEST['save'];

        $packid = md5($json);

        $dir = Sprint\Editor\Module::getPacksDir();

        file_put_contents($dir. $packid . '.json', $json);

        $packs = \Sprint\Editor\Editor::registerPacks();

        $current = 'pack_' . $packid;
        
        $result = array(
            'current' => $current,
            'select' => $packs,
        );

    }


    if (isset($_REQUEST['load'])){
        $packid = $_REQUEST['load'];

        $dir = Sprint\Editor\Module::getPacksDir();

        $result = file_get_contents($dir. $packid . '.json', $json);

        $result = json_decode($result, true);

    }

    if (isset($_REQUEST['del'])){
        $packid = $_REQUEST['del'];

        $dir = Sprint\Editor\Module::getPacksDir();

        $file = $dir. $packid . '.json';

        if (is_file($file)){
            unlink($file);
        }

        $packs = \Sprint\Editor\Editor::registerPacks();

        $current = '';

        if (!empty($packs['blocks'])){
            $current = $packs['blocks'][0]['name'];
        }

        $result = array(
            'current' => $current,
            'select' => $packs,

        );

    }

}


header('Content-type: application/json; charset=utf-8');
echo json_encode($result);



require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");