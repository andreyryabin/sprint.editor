<?php

namespace Sprint\Editor;

class Task0003 extends Upgrade
{

    public function __construct() {
        $this->addButton('execute', GetMessage('SPRINT_EDITOR_BTN_EXECUTE'));
        $this->addButton('check', GetMessage('SPRINT_EDITOR_BTN_CHECK'));

        $this->setDescription('Увеличить размер колонки в бд до longtext');

        \CModule::IncludeModule('iblock');
    }


    public function execute() {
        $updated = 0;

        $props = $this->findProps('sprint_editor', 'text');

        foreach ($props as $prop) {
            if ($this->modifyProp($prop['IBLOCK_ID'], $prop['ID'], 'longtext')) {
                $updated++;
            }
        }

        $this->out('found: %d updated:%d',
            count($props),
            $updated
        );

    }


    public function check() {
        $props = $this->findProps('sprint_editor', 'text');
        foreach ($props as $prop) {
            $this->out('Found %d:%d %s', $prop['IBLOCK_ID'], $prop['ID'], $prop['CODE']);
        }

        if (count($props) <= 0) {
            $this->out('Props for update not found');
        }

    }

    protected function findProps($propertyType, $columnType) {
        global $DB;

        $dbres = \CIBlockProperty::GetList(['SORT' => 'ASC'], [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => $propertyType,
            'VERSION' => 2,
            'MULTIPLE' => 'N'
        ]);

        $props = array();
        while ($item = $dbres->Fetch()) {
            $column = $DB->Query(sprintf(
                'SHOW COLUMNS FROM `b_iblock_element_prop_s%d` WHERE Field="PROPERTY_%d" AND Type="%s"',
                $item['IBLOCK_ID'],
                $item['ID'],
                $columnType
            ), true)->Fetch();

            if (!empty($column)) {
                $props[] = $item;
            }
        }

        return $props;
    }


    protected function modifyProp($iblockId, $id, $columnType) {
        global $DB;

        $ok = $DB->Query(sprintf(
            'ALTER TABLE `b_iblock_element_prop_s%d` MODIFY `PROPERTY_%d` %s',
            $iblockId,
            $id,
            $columnType

        ), true);

        return !empty($ok);
    }

}