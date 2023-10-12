<?php

namespace Sprint\Editor\Blocks;

use CIBlockElement;
use CModule;

class IblockElements
{
    /**
     * @param array $block   Содержимое блока
     * @param array $params  Массив с параметрами запроса <br>
     *                       "select" => ["Список полей для выборки"],<br>
     *                       "filter" =>  ["Фильтры"],<br>
     *
     * @return array
     */
    static public function getList($block, $params = [])
    {
        CModule::IncludeModule('iblock');

        if (empty($block['iblock_id']) || empty($block['element_ids'])) {
            return [];
        }

        if (isset($params['select']) || isset($params['filter'])) {
            $select = $params['select'] ?? [];
            $filter = $params['filter'] ?? [];
        } else {
            $select = $params;
            $filter = [];
        }

        $select = array_merge([
            'ID',
            'IBLOCK_ID',
            'NAME',
            'SORT',
            'ACTIVE',
            'DETAIL_PAGE_URL',
            'PREVIEW_TEXT',
        ], $select);

        $filter = array_merge([
            'IBLOCK_ID' => $block['iblock_id'],
            'ID'        => $block['element_ids'],
        ], $filter);

        $dbRes = CIBlockElement::GetList(
            [],
            $filter,
            false,
            false,
            $select
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
