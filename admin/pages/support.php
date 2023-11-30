<?php
/** @global $APPLICATION CMain */
global $APPLICATION;
$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_SUPPORT'));
$APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/support_page.js');

$content = file_get_contents(
    'https://andreyryabin.github.io/sprint_editor/support.html',
    false,
    stream_context_create(['http' => ['timeout' => 10]])
);

echo $content;
