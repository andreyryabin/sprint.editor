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

if (CModule::IncludeModule('sprint.editor')) {
    $url = !empty($_REQUEST['url']) ? trim($_REQUEST['url']) : '';

    $videoHtml = '';
    $services = [
        'youtube' => Sprint\Editor\Tools\Youtube::class,
        'vimeo'   => Sprint\Editor\Tools\Vimeo::class,
    ];

    foreach ($services as $code => $service) {
        $videoHtml = $service::getVideoHtml($url, 320, 180);
        if ($videoHtml) {
            break;
        }
    }

    echo json_encode(
        [
            'url'  => $url,
            'html' => $videoHtml,
        ]
    );
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
