<?php
global $APPLICATION;

use Bitrix\Main\Loader;

if (!Loader::includeModule('sprint.editor')) {
    return false;
}

if ($APPLICATION->GetGroupRight('sprint.editor') == 'D') {
    return false;
}

$aMenu = [
    'parent_menu' => 'global_menu_settings',
    'section'     => 'Sprint',
    'sort'        => 51,
    'text'        => GetMessage('SPRINT_EDITOR_MODULE_NAME'),
    'icon'        => 'sys_menu_icon',
    'page_icon'   => 'sys_page_icon',
    'items_id'    => 'sprint_editor',
    'items'       => [
        [
            'text' => GetMessage('SPRINT_EDITOR_PACKS_PAGE'),
            'url'  => 'sprint_editor.php?' . http_build_query(
                    [
                        'lang' => LANGUAGE_ID,
                    ]
                ),
        ],
    ],
];

return $aMenu;
