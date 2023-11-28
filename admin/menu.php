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
                        'lang'     => LANGUAGE_ID,
                        'showpage' => 'packs_builder',
                    ]
                ),
        ],
        [
            'text' => GetMessage('SPRINT_EDITOR_COMPLEX_BUILDER'),
            'url'  => 'sprint_editor.php?' . http_build_query(
                    [
                        'lang'     => LANGUAGE_ID,
                        'showpage' => 'complex_builder',
                    ]
                ),
        ],
        /*[
            'text' => GetMessage('SPRINT_EDITOR_TRASH_FILES'),
            'url'  => 'sprint_editor.php?' . http_build_query(
                    [
                        'lang'     => LANGUAGE_ID,
                        'showpage' => 'trash_files',
                    ]
                ),
        ],*/
    ],
];

return $aMenu;
