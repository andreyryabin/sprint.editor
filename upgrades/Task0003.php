<?php

namespace Sprint\Editor;

use CIBlockProperty;
use CModule;

class Task0003 extends Upgrade
{
    public function __construct()
    {
        $this->addButton('execute', GetMessage('SPRINT_EDITOR_BTN_EXECUTE'));
        $this->addButton('check', GetMessage('SPRINT_EDITOR_BTN_CHECK'));

        $this->setDescription(GetMessage('SPRINT_EDITOR_LONG_TEXT_DESC'));

        CModule::IncludeModule('iblock');
    }

    public function execute()
    {
        $updated = 0;

        $props = $this->findProps('sprint_editor', 'text');

        foreach ($props as $prop) {
            if ($this->modifyProp($prop['IBLOCK_ID'], $prop['ID'], 'longtext')) {
                $updated++;
            }
        }

        $this->out(
            GetMessage(
                'SPRINT_EDITOR_LONG_TEXT_PROP_UPDATED', [
                    '#COUNT#'   => count($props),
                    '#UPDATED#' => $updated,
                ]
            )
        );
    }

    public function check()
    {
        $props = $this->findProps('sprint_editor', 'text');
        $messages = '';
        foreach ($props as $prop) {
            $messages .= GetMessage(
                             'SPRINT_EDITOR_LONG_TEXT_PROP_FOUND', [
                                 '#PROP_NAME#'   => $prop['ID'],
                                 '#IBLOCK_NAME#' => $prop['IBLOCK_ID'],
                             ]
                         ) . PHP_EOL;
        }

        if (count($props) > 0) {
            $this->out(nl2br($messages));
        } else {
            $this->out(GetMessage('SPRINT_EDITOR_LONG_TEXT_PROPS_NOT_FOUND'));
        }
    }

    protected function findProps($propertyType, $columnType)
    {
        global $DB;

        $dbres = CIBlockProperty::GetList(
            ['SORT' => 'ASC'], [
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE'     => $propertyType,
                'VERSION'       => 2,
                'MULTIPLE'      => 'N',
            ]
        );

        $props = [];
        while ($item = $dbres->Fetch()) {
            $column = $DB->Query(
                sprintf(
                    'SHOW COLUMNS FROM `b_iblock_element_prop_s%d` WHERE Field="PROPERTY_%d" AND Type="%s"',
                    $item['IBLOCK_ID'],
                    $item['ID'],
                    $columnType
                ), true
            )->Fetch();

            if (!empty($column)) {
                $props[] = $item;
            }
        }

        return $props;
    }

    protected function modifyProp($iblockId, $id, $columnType)
    {
        global $DB;

        $ok = $DB->Query(
            sprintf(
                'ALTER TABLE `b_iblock_element_prop_s%d` MODIFY `PROPERTY_%d` %s',
                $iblockId,
                $id,
                $columnType

            ), true
        );

        return !empty($ok);
    }
}
