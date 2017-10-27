<?php
/*
 * этот файл перезаписывается при обновлениях
 * используйте его в качестве примера
 */

$settings = array(
    'title' => 'Пример настройки',

    'layout_classes' => array(
        'type1' => array(
            array('col-md-8', 'col-md-9', 'col-md-12'),
            array('col-sm-8', 'col-sm-9', 'col-sm-12'),
            array('col-xs-8', 'col-xs-9', 'col-xs-12'),
            array('col-lg-8', 'col-lg-9', 'col-lg-12'),
        ),
        'type2' => array(
            array('col-md-3', 'col-md-4', 'col-md-5', 'col-md-6', 'col-md-7', 'col-md-8', 'col-md-9'),
            array('col-sm-3', 'col-sm-4', 'col-sm-5', 'col-sm-6', 'col-sm-7', 'col-sm-8', 'col-sm-9'),
            array('col-xs-3', 'col-xs-4', 'col-xs-5', 'col-xs-6', 'col-xs-7', 'col-xs-8', 'col-xs-9'),
            array('col-lg-3', 'col-lg-4', 'col-lg-5', 'col-lg-6', 'col-lg-7', 'col-lg-8', 'col-lg-9'),
        ),
        'type3' => array(
            array('col-md-3', 'col-md-4', 'col-md-5', 'col-md-6', 'col-md-7', 'col-md-8'),
            array('col-sm-3', 'col-sm-4', 'col-sm-5', 'col-sm-6', 'col-sm-7', 'col-sm-8'),
            array('col-xs-3', 'col-xs-4', 'col-xs-5', 'col-xs-6', 'col-xs-7', 'col-xs-8'),
            array('col-lg-3', 'col-lg-4', 'col-lg-5', 'col-lg-6', 'col-lg-7', 'col-lg-8'),
        ),
        'type4' => array(
            array('col-md-3', 'col-md-4', 'col-md-5', 'col-md-6', 'col-md-7'),
            array('col-sm-3', 'col-sm-4', 'col-sm-5', 'col-sm-6', 'col-sm-7'),
            array('col-xs-3', 'col-xs-4', 'col-xs-5', 'col-xs-6', 'col-xs-7'),
            array('col-lg-3', 'col-lg-4', 'col-lg-5', 'col-lg-6', 'col-lg-7'),
        ),
    ),

    'layout_defaults' => array(
        'type1' => '',
        'type2' => 'col-md-6',
        'type3' => 'col-md-4',
        'type4' => 'col-md-3',
    ),

    'block_settings' => array(
        'text' => array(
            'param1' => array(
                'type' => 'select',
                'value' => array(
                    'style1' => 'Стиль 1',
                    'style2' => 'Стиль 2',
                    'style3' => 'Стиль 3',
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

    'block_enabled' => array(
        'htag',
        'text',
        'gallery'
    ),


);