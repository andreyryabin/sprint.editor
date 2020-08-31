<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

/*
 *
 * Пример создания содержимого редактора через апи модуля
 * И сохранение его в элемент инфоблока
 *
 */

CModule::IncludeModule('iblock');
CModule::IncludeModule('sprint.editor');

use Sprint\Editor\Structure\Structure;

$json = (new Structure())
    //->fromJson()
    //->fromArray()

    ->addLayout([
        'param1'=>'value1'
    ])
    ->addColumn(
        [
            'css' => 'col-md-3',
        ]
    )
    ->addBlock(
        [
            'name'  => 'htag',
            'value' => 'Заголовок 1',
        ]
    )
    ->addColumn()
    ->addBlock(
        [
            'name'  => 'text',
            'value' => 'Текст на второй колонке',
        ]
    )
    ->addLayout()
    ->addColumn()
    ->addBlock(
        [
            'name'  => 'text',
            'value' => 'Текст 2',
        ]
    )
    //->toArray()
    ->toJson();

/*
 * Сохраняем в элемент с id=200 в инфоблоке с id=16
 * cодержимое редактора в свойство с кодом EDITOR1
 *
 */
CIBlockElement::SetPropertyValuesEx(
    200, 16, [
    'EDITOR1' => $json,
]
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
