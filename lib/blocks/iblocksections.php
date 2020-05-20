<?php
namespace Sprint\Editor\Blocks;

class IblockSections
{

    static public function getList($block, $select = array()){
        if (empty($block['iblock_id']) || empty($block['section_ids'])){
            return array();
        }

        \CModule::IncludeModule('iblock');

        $select = array_merge(array(
            'ID',
            'IBLOCK_ID',
            'NAME',
            'SORT',
            'ACTIVE',
            'SECTION_PAGE_URL',
        ), $select);

        $dbRes = \CIBlockSection::GetList(array(), array(
            'IBLOCK_ID' => $block['iblock_id'],
            'ID' => $block['section_ids']
        ),false,$select);

        $unsorted = array();
        while ($aItem = $dbRes->GetNext()){
            $unsorted[ $aItem['ID'] ] = $aItem;
        }

        $result = array();
        foreach ($block['section_ids'] as $id){
            if (isset($unsorted[$id])){
                $result[] = $unsorted[$id];
            }
        }

        return $result;

    }

}
