<?php

namespace Sprint\Editor\Blocks;

use CIBlockElement;
use CModule;

class IblockElements
{
    static public function getList($block, $select = [])
    {
        if (empty($block['iblock_id']) || empty($block['element_ids'])) {
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
                'DETAIL_PAGE_URL',
                'PREVIEW_TEXT',
            ], $select
        );

        $dbRes = CIBlockElement::GetList(
            [], [
            'IBLOCK_ID' => $block['iblock_id'],
            'ID'        => $block['element_ids'],
        ], false, false, $select
        );

        $unsorted = [];
        while ($aItem = $dbRes->GetNext()) {
            $unsorted[$aItem['ID']] = $aItem;
        }

        $result = [];
        foreach ($block['element_ids'] as $id) {
            if (isset($unsorted[$id])) {
                $result[] = $unsorted[$id];
            }
        }

        return $result;
    }
}
