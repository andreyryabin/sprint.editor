<?php

namespace Sprint\Editor\Blocks;

use CIBlockElement;
use CIBlockSection;
use CModule;

class IblockSections
{
    /**
     * @param array $block   Содержимое блока
     * @param array $params  Массив с параметрами запроса <br>
     *                       "select" => ["Список полей для выборки"],<br>
     *                       "filter" =>  ["Фильтры"],<br>
     *
     * @return array
     */
    static public function getList(
        $block,
        $params = [],
    ) {
        CModule::IncludeModule('iblock');

        if (empty($block['iblock_id']) || empty($block['section_ids'])) {
            return [];
        }

        if (isset($params['select']) || isset($params['filter'])) {
            $select = $params['select'] ?? [];
            $filter = $params['filter'] ?? [];
        } else {
            $select = $params;
            $filter = [];
        }

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
            [],
            $filter,
            false,
            $select
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

    /**
     * @param array $block  Содержимое блока
     * @param array $params Массив с параметрами запроса <br>
     *                      "select" => ["Список полей для выборки"],<br>
     *                      "filter" =>  ["Фильтры"],<br>
     *                      "order" => ["Сортировка"],<br>
     *                      "navParams" => ["Постраничная навигация"],<br>
     *
     * @return array
     */
    static public function getElements(
        $block,
        $params = []
    ) {
        CModule::IncludeModule('iblock');
        if (empty($block['iblock_id']) || empty($block['section_ids'])) {
            return [];
        }

        $select = $params['select'] ?? [];
        $filter = $params['filter'] ?? ['ACTIVE' => 'Y'];
        $order = $params['order'] ?? ['SORT' => 'ASC'];
        $navParams = $params['navParams'] ?? ['nTopCount' => 20];

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
            $order,
            $filter,
            false,
            $navParams,
            $select
        );

        $result = [];
        while ($aItem = $dbRes->GetNext()) {
            $result[] = $aItem;
        }
        return $result;
    }
}
