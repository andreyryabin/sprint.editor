<?php

namespace Sprint\Editor\Blocks;

use CIBlockSection;
use CModule;

class IblockSections
{
    static public function getList($block, $select = [])
    {
        if (empty($block['iblock_id']) || empty($block['section_ids'])) {
            return [];
        }

        CModule::IncludeModule('iblock');

        $select = array_merge(
            [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'SORT',
                'ACTIVE',
                'SECTION_PAGE_URL',
            ], $select
        );

        $dbRes = CIBlockSection::GetList(
            [], [
            'IBLOCK_ID' => $block['iblock_id'],
            'ID'        => $block['section_ids'],
        ], false, $select
        );

        $unsorted = [];
        while ($aItem = $dbRes->GetNext()) {
            $unsorted[$aItem['ID']] = $aItem;
        }

        $result = [];
        foreach ($block['section_ids'] as $id) {
            if (isset($unsorted[$id])) {
                $result[] = $unsorted[$id];
            }
        }

        return $result;
    }
}
