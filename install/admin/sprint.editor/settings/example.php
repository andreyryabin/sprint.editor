<?php
/*
 * этот файл перезаписывается при обновлениях
 * используйте его в качестве примера
 */

$settings = [
    'title'           => 'Редактор с колонками',

    //Разрешить изменение структуры материала, перекрывает настройку "Отключить добавление блоков"
    //'enable_change' => true,

    //Удалять блок при перемещении его за пределы редактора
    //'delete_block_after_sort_out' => false,

    //Пример пользовательских настроек для сеток
    'layout_settings' => [
        'type1' => [],
        'type2' => [
            //            'param1' => [
            //                'type'    => 'select',
            //                'default' => 'style1',
            //                'value'   => [
            //                    'style1' => 'style1',
            //                    'style2' => 'style2',
            //                ],
            //            ],
            //            'param2' => [
            //                'type'  => 'select',
            //                'value' => [
            //                    'style3' => 'style3',
            //                    'style4' => 'style4',
            //                ],
            //            ],
        ],
        'type3' => [],
        'type4' => [],
    ],

    //Доступные классы колонок для сеток
    'layout_classes'  => [
        'type1' => [],
        'type2' => [
            ['col-md-3', 'col-md-4', 'col-md-6', 'col-md-8', 'col-md-9', 'col-md-12'],
        ],
        'type3' => [
            ['col-md-3', 'col-md-4', 'col-md-6', 'col-md-8', 'col-md-9', 'col-md-12'],
        ],
        'type4' => [
            ['col-md-3', 'col-md-4', 'col-md-6', 'col-md-8', 'col-md-9', 'col-md-12'],
        ],
    ],

    //Классы колонок для сеток по умолчанию
    'layout_defaults' => [
        'type1' => '',
        'type2' => 'col-md-6',
        'type3' => 'col-md-4',
        'type4' => 'col-md-3',
    ],

    //Названия классов для колонок
    'layout_titles'   => [
        'col-md-12' => '100%',
        'col-md-9'  => '75%',
        'col-md-8'  => '66.66%',
        'col-md-6'  => '50%',
        'col-md-4'  => '33.33%',
        'col-md-3'  => '25%',
    ],

    //Произвольный список блоков
    //    'block_toolbar' => [
    //        [
    //            'title'  => 'Блоки1',
    //            'blocks' => [
    //                'htag',
    //                'text',
    //            ],
    //        ],
    //        [
    //            'title'  => 'Блоки2',
    //            'blocks' => [
    //                'gallery',
    //                'image',
    //            ],
    //        ],
    //    ],

    //Настройки блоков
    'block_settings'  => [
        'htag'      => [
            //список тегов для заголовка
            'taglist' => [
                'type'  => 'hidden',
                'value' => [
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                    'h5' => 'h5',
                ],
            ],

        ],
        'accordion' => [
            //подключаемые блоки к аккордеону
            'blocks' => [
                'type'  => 'hidden',
                'value' => [
                    'text'  => 'текст',
                    'image' => 'картинку',
                    'video' => 'видео',
                ],
            ],
        ],
        'text'      => [
            'param1' => [
                'type'  => 'select',
                'value' => [
                    'style1' => 'Цитата',
                    //'style2' => 'Сноска',
                ],
            ],

            'csslist' => [
                'type'  => 'hidden',
                'value' => [
                    'sp-text-1' => 'Стиль 1',
                    'sp-text-2' => 'Стиль 2',
                    'sp-text-3' => 'Стиль 3',
                ],
            ],
        ],
        'lists'     => [
            'type' => [
                'type'    => 'select',
                'default' => 'ul',
                'value'   => [
                    'ul' => 'Маркированный',
                    'ol' => 'Нумерованный',
                ],
            ],
        ],
    ],

    //Отключить указанные блоки (если используется, то block_enabled уже не обрабатывается)
    'block_disabled'  => [
        //'slideshare'
    ],

    //Разрешить добавление указанных блоков
    'block_enabled'   => [
        //        'htag',
        //        'text',
        //        'gallery'
    ],

    //Разрешить добавление указанных сеток
    'layout_enabled'  => [
        //        'layout_1',
        //        'layout_2',
        //        'layout_3',
        //        'layout_4',
    ],

];
