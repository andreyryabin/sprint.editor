<?php return [
    'jquery_version'       => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_JQUERY_VERSION'),
        'DEFAULT' => 'jquery3',
        'TYPE'    => 'select',
        'ITEMS'   => [
            'jquery'  => 'jquery1',
            'jquery3' => 'jquery3',
        ],
    ],
    'load_jquery_ui'       => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_LOAD_JQUERY_UI'),
        'DEFAULT' => 'yes',
        'TYPE'    => 'checkbox',
    ],
    'load_dotjs'           => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_LOAD_DOTJS'),
        'DEFAULT' => 'yes',
        'TYPE'    => 'checkbox',
    ],
    'show_support'         => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_SHOW_SUPPORT'),
        'DEFAULT' => 'yes',
        'TYPE'    => 'checkbox',
    ],
    'show_trash_files'     => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_SHOW_TRASH_FILES'),
        'DEFAULT' => 'no',
        'TYPE'    => 'checkbox',
    ],
    'instagram_app_id'     => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_INSTAGRAM_APP_ID'),
        'DEFAULT' => '2741760692768967',
        'TYPE'    => 'text',
    ],
    'instagram_app_secret' => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_INSTAGRAM_APP_SECRET'),
        'DEFAULT' => '828e97ef193404e336cbc1e7e9628412',
        'TYPE'    => 'text',
    ],
    'flickr_api_key'       => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_FLICKR_API_KEY'),
        'DEFAULT' => '',
        'TYPE'    => 'text',
    ],
    'flickr_user_id'       => [
        'TITLE'   => GetMessage('SPRINT_EDITOR_FLICKR_USER_ID'),
        'DEFAULT' => '',
        'TYPE'    => 'text',
    ],
];
