<?php
/** @global $APPLICATION CMain */

use Bitrix\Main\Page\Asset;

global $APPLICATION;
$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_SUPPORT'));
Asset::getInstance()->addJs('/bitrix/admin/sprint.editor/assets/support_page.js');

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://andreyryabin.github.io/sprint_editor/support.html');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    echo curl_exec($ch);
    curl_close($ch);
}
