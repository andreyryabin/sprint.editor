<?php
global $APPLICATION;

use Bitrix\Main\Loader;
use Sprint\Editor\Module;

if (!Loader::includeModule('sprint.editor')) {
    return false;
}

if ($APPLICATION->GetGroupRight('sprint.editor') == 'D') {
    return false;
}

$items = [];
$items[] = [
    'text' => GetMessage('SPRINT_EDITOR_PACKS_PAGE'),
    'url'  => 'sprint_editor.php?' . http_build_query(
            [
                'lang'     => LANGUAGE_ID,
                'showpage' => 'packs_builder',
            ]
        ),
];
$items[] = [
    'text' => GetMessage('SPRINT_EDITOR_COMPLEX_BUILDER'),
    'url'  => 'sprint_editor.php?' . http_build_query(
            [
                'lang'     => LANGUAGE_ID,
                'showpage' => 'complex_builder',
            ]
        ),
];
if (Module::getDbOption('show_trash_files') == 'yes') {
    $items[] = [
        'text' => GetMessage('SPRINT_EDITOR_TRASH_FILES'),
        'url'  => 'sprint_editor.php?' . http_build_query(
                [
                    'lang'     => LANGUAGE_ID,
                    'showpage' => 'trash_files',
                ]
            ),
    ];
}
if (Module::getDbOption('show_support') == 'yes') {
    $items[] = [
        'text' => GetMessage('SPRINT_EDITOR_SUPPORT'),
        'url'  => 'sprint_editor.php?' . http_build_query(
                [
                    'lang'     => LANGUAGE_ID,
                    'showpage' => 'support',
                ]
            ),
    ];
}

if (empty($items)) {
    return [];
}

$aMenu = [
    'parent_menu' => 'global_menu_settings',
    'section'     => 'Sprint',
    'sort'        => 51,
    'text'        => GetMessage('SPRINT_EDITOR_MODULE_NAME'),
    'icon'        => 'sys_menu_icon',
    'page_icon'   => 'sys_page_icon',
    'items_id'    => 'sprint_editor',
    'items'       => $items,
];

return $aMenu;
