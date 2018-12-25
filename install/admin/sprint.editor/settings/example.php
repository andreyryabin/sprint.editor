<?php
/*
 * этот файл перезаписывается при обновлениях
 * используйте его в качестве примера
 */

$settings = array(
    'title' => 'Пример настройки',

    //Разрешить изменение структуры материала, перекрывает настройку "Отключить добавление блоков"
    //'enable_change' => true,

    //Разрешить изменение числа колонок в сетке, работает если настройка enable_change включена
    //'enable_change_columns' => false,

    //Доступные классы колонок для сеток
    'layout_classes' => array(
        'type1' => array(
            array('col-md-3', 'col-md-4', 'col-md-6', 'col-md-8', 'col-md-9', 'col-md-12')
        ),
        'type2' => array(
            array('col-md-3', 'col-md-4', 'col-md-6', 'col-md-8', 'col-md-9', 'col-md-12'),
        ),
        'type3' => array(
            array('col-md-3', 'col-md-4', 'col-md-6', 'col-md-8', 'col-md-9', 'col-md-12'),
        ),
        'type4' => array(
            array('col-md-3', 'col-md-4', 'col-md-6', 'col-md-8', 'col-md-9', 'col-md-12'),
        ),
    ),

    //Классы колонок для сеток по умолчанию
    'layout_defaults' => array(
        'type1' => '',
        'type2' => 'col-md-6',
        'type3' => 'col-md-4',
        'type4' => 'col-md-3',
    ),

    //Названия классов для колонок
    'layout_titles' => array(
        'col-md-12' => '100%',
        'col-md-9' => '75%',
        'col-md-8' => '66.66%',
        'col-md-6' => '50%',
        'col-md-4' => '33.33%',
        'col-md-3' => '25%',
    ),

    //Настройки блоков
    'block_settings' => array(
        'text' => array(
            'param1' => array(
                'type' => 'select',
                'value' => array(
                    'style1' => 'Цитата',
                    'style2' => 'Сноска',
                )
            ),

            'csslist' => array(
                'type' => 'hidden',
                'value' => array(
                    'sp-text-1' => 'Стиль 1',
                    'sp-text-2' => 'Стиль 2',
                    'sp-text-3' => 'Стиль 3',
                )
            ),
        ),

        'image' => array(
            'param1' => array(
                'type' => 'select',
                'value' => array(
                    'style1' => 'Стиль 1',
                    'style2' => 'Стиль 2',
                    'style3' => 'Стиль 3',
                )
            ),
        ),

    ),

    //Разрешить добавление указанных блоков
    'block_enabled' => array(
//        'htag',
//        'text',
//        'gallery'
    ),


    //Разрешить добавление указанных сеток
    'layout_enabled' => array(
//        'layout_1',
//        'layout_2',
//        'layout_3',
//        'layout_4',
    ),



);