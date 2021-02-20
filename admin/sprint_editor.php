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

$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_PACKS_PAGE'));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

include __DIR__ . '/includes/interface.php';

/** @noinspection PhpIncludeInspection */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
