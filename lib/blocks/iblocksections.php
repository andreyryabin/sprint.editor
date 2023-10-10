<?php

namespace Sprint\Editor\Blocks;

use CIBlockElement;
use CIBlockSection;
use CModule;

class IblockSections
{
    static public function getList(
        $block,
        $select = [],
        $filter = []
    ) {
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

        $filter = array_merge([
            'IBLOCK_ID' => $block['iblock_id'],
            'ID'        => $block['section_ids'],
        ], $filter);

        $dbRes = CIBlockSection::GetList(
            [], $filter, false, $select
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

    static public function getElements(
        $block,
        $select = [],
        $filter = ['ACTIVE' => 'Y'],
        $navParams = ['nTopCount' => 20]
    ) {
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
                'DETAIL_PAGE_URL',
                'PREVIEW_TEXT',
            ], $select
        );

        $filter = array_merge([
            'IBLOCK_ID'  => $block['iblock_id'],
            'SECTION_ID' => $block['section_ids'],
        ], $filter);

        $dbRes = CIBlockElement::GetList(
            [], $filter, false, $navParams, $select
        );

        $result = [];
        while ($aItem = $dbRes->GetNext()) {
            $result[] = $aItem;
        }
        return $result;
    }
}
