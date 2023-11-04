<?php

/** @noinspection PhpIncludeInspection */

use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

/** @global $APPLICATION CMain */
global $APPLICATION;

if (!Loader::includeModule('sprint.editor')) {
    return false;
}

if ($APPLICATION->GetGroupRight('sprint.editor') == 'D') {
    return false;
}

$APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/admin_pages.css?2');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$request = Bitrix\Main\Context::getCurrent()->getRequest();

$showpage = preg_replace("/[^a-z0-9_]/", "", $request->get('showpage'));

if ($showpage && file_exists(__DIR__ . '/pages/' . $showpage . '.php')) {
    include __DIR__ . '/pages/' . $showpage . '.php';
}

/** @noinspection PhpIncludeInspection */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
