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

    $sitetemplates = \CHTMLEditor::GetSiteTemplates();
    $components = \CHTMLEditor::GetComponents(array(), false, array());

    $groups = array();
    $nslist = array();

    $mask = !empty($_REQUEST['filter_mask']) ? trim($_REQUEST['filter_mask'])  : '';

    foreach ($components['items'] as $item){
        if ($item['complex'] == 'Y'){
            continue;
        }

        if ($mask && 0 !== strpos($item['name'],$mask)){
            continue;
        }

        $p = explode(':',$item['name']);
        $ns = $p[0];

        if (!in_array($ns, $nslist)){
            $nslist[] = $ns;
        }

        $index = array_search($ns, $nslist);

        if (!isset($groups[$index])){
            $groups[$index] = array(
                'name' => $ns,
                'items' => array()
            );
        }

        $groups[$index]['items'][] = $item;
    }

    $result = array(
        'components' => $groups,
        'sitetemplates' => $sitetemplates,
    );

    echo json_encode(\Sprint\Editor\Locale::convertToUtf8IfNeed($result));
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");