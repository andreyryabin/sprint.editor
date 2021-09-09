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
    header('Content-type: application/json; charset=utf-8');

    CModule::IncludeModule('fileman');

    $clearCache = (!empty($_REQUEST['clear_cache']));

    $sitetemplates = CHTMLEditor::GetSiteTemplates();
    $allcomponents = CHTMLEditor::GetComponents([], $clearCache, []);

    $filtered = [];
    $nslist = [];

    $maskInc = !empty($_REQUEST['filter_include']) ? trim($_REQUEST['filter_include']) : '';
    $maskExc = !empty($_REQUEST['filter_exclude']) ? trim($_REQUEST['filter_exclude']) : '';

    $curComp = !empty($_REQUEST['component_name']) ? trim($_REQUEST['component_name']) : '';
    $curSite = !empty($_REQUEST['filter_site']) ? trim($_REQUEST['filter_site']) : '';

    foreach ($allcomponents['items'] as $item) {
        if ($item['complex'] == 'Y') {
            continue;
        }

        if ($maskInc && 0 !== strpos($item['name'], $maskInc)) {
            continue;
        }

        if ($maskExc && 0 === strpos($item['name'], $maskExc)) {
            continue;
        }

        $nparts = explode(':', $item['name']);
        $ns = $nparts[0];

        if (!in_array($ns, $nslist)) {
            $nslist[] = $ns;
        }

        $index = array_search($ns, $nslist);

        if (!isset($filtered[$index])) {
            $filtered[$index] = [
                'name'  => $ns,
                'items' => [],
            ];
        }

        $filtered[$index]['items'][] = $item;
    }

    $result = [
        'components'     => $filtered,
        'sitetemplates'  => $sitetemplates,
        'component_name' => $curComp,
        'filter_site'    => $curSite,
        'filter_include' => $maskInc,
        'filter_exclude' => $maskExc,
    ];

    echo json_encode(\Sprint\Editor\Locale::convertToUtf8IfNeed($result));
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
