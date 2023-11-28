<?php
/*
 * этот файл перезаписывается при обновлениях
 * используйте его в качестве примера
 */

$settings = [
    'title'            => 'Редактор с колонками',

    //Разрешить изменение структуры материала, перекрывает настройку "Отключить добавление блоков"
    //'enable_change' => true,

    //Развернуть редактор на всю ширину страницы
    //'wide_mode'     => true,

    //Разрешить добавление указанных сеток
    'layout_enabled'   => [
        'layout_1',
        'layout_2',
        'layout_3',
        'layout_4',
    ],

    //Названия сеток переопределяющие значения по умолчанию
    'layout_titles'    => [
        'type2' => 'Сетка из 2х колонок',
        'type5' => 'Сетка из 5и колонок',
    ],

    //Пример пользовательских настроек для сеток
    'layout_settings'  => [
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

    //Названия классов для колонок
    'classes_titles'   => [
        'col-md-12' => '100%',
        'col-md-9'  => '75%',
        'col-md-8'  => '66.66%',
        'col-md-6'  => '50%',
        'col-md-4'  => '33.33%',
        'col-md-3'  => '25%',
    ],

    //Доступные классы колонок для сеток
    'layout_classes'   => [
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
    'layout_defaults'  => [
        'type1' => '',
        'type2' => 'col-md-6',
        'type3' => 'col-md-4',
        'type4' => 'col-md-3',
    ],

    //Названия блоков переопределяющие значения по умолчанию
    'block_titles'     => [
        //'text' => 'Текст',
    ],
    /* параметр заменяет значения по умолчанию, заданные в файлах config.json блоков
    'block_configs'  => [
        'text' => [
             //html-вид кнопки вставки блока
            'button'  => 'Текст <br/><img src="/local/admin/sprint.editor/settings/icons/text_icon.png">',
            //новый заголовок
            'title' => 'Текст1',
            'hint'  => 'Подсказка при наведении на название блока',
            'description'  => 'Видимая подсказка под блоком',
        ],
    ],*/
    //сортировка по названию блока
    //'block_sort'     => 'title',

    //сортировка по полю sort (по умолчанию)
    //'block_sort'     => 'sort',

    //Произвольный список блоков (отключает сортировку, выводит блоки в заданном порядке)
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
    'block_settings'   => [
        'htag'            => [
            //текстовое поле ввода
            /*
           'param1'     => [
               'type'  => 'text',
               //Заголовок настройки (необязательное)
               'title' => 'Css',
               //Значение по умолчанию (необязательное)
               'default => 'my-css-123'
           ],
           //выпадающий список
           'param2' => [
               'type'  => 'dropdown',
               'title' => 'Param2',
               'default => 'xx'
               'value' => [
                   'xx' => 'xx',
                   'yy' => 'yy',
               ],
           ],*/
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
        'accordion'       => [
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
        'text'            => [
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

            'toolbar' => [
                'type'  => 'hidden',
                'value' => [
                    [
                        'viewHTML',
                        'link',
                        'strong',
                        'em',
                        'underline',
                        'del',
                    ],
                    [
                        'justifyLeft',
                        'justifyCenter',
                        'justifyRight',
                    ],
                ],
            ],
        ],
        'lists'           => [
            'type' => [
                'type'    => 'select',
                'default' => 'ul',
                'value'   => [
                    'ul' => 'Маркированный',
                    'ol' => 'Нумерованный',
                ],
            ],
        ],
        /*'iblock_elements' => [
            //ограничим список доступных ИБ в блоке "Инфоблоки.Элементы"
            'enabled_iblocks' => [
                'type'  => 'hidden',
                'value' => [4, 10],
            ],
            //множественный выбор true|false, по умолчанию true
            'multiple' => [
                'type'  => 'hidden',
                'value' => false,
            ]
        ],
        'iblock_sections' => [
            //ограничим список доступных ИБ в блоке "Инфоблоки.Категории"
            'enabled_iblocks' => [
                'type'  => 'hidden',
                'value' => [4, 10],
            ],
            //множественный выбор true|false, по умолчанию true
            'multiple' => [
                'type'  => 'hidden',
                'value' => false,
            ]
        ],*/
    ],

    //Настройки блоков внутри составных
    'complex_settings' => [
        //название составного блока
        'complex_image_text' => [
            //название вложенного блока
            'text' => [
                //название настройки вложенного блока
                //поддерживается только 'type'=>'hidden'
                'toolbar' => [
                    'type'  => 'hidden',
                    'value' => [
                        [
                            'viewHTML',
                            'link',
                            'strong',
                            'em',
                            'underline',
                            'del',
                        ],
                        [
                            'justifyLeft',
                            'justifyCenter',
                            'justifyRight',
                        ],
                    ],
                ],
            ],
        ],
    ],

    //Отключить указанные блоки (если используется, то block_enabled уже не обрабатывается)
    'block_disabled'   => [
        //'slideshare'
    ],

    //Разрешить добавление указанных блоков
    'block_enabled'    => [
        //        'htag',
        //        'text',
        //        'gallery'
    ],

    //Сниппеты для блока "Сниппет"
    //Файлы сниппетов хранятся в папке /local/admin/sprint.editor/snippets или в /bitrix/admin/sprint.editor/snippets
    //Данный путь можно переопределить в шаблоне блока snippet.php
    'snippets'         => [
        [
            'file'        => 'example.php',
            'title'       => 'example',
            'description' => '<strong>Пример сниппета</strong>',
        ],
    ],
];
