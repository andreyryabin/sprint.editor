<?php
namespace Sprint\Editor\Blocks;

class IblockElements
{

    static public function getList($block, $select = array()){
        if (empty($block['iblock_id']) || empty($block['element_ids'])){
            return array();
        }

        \CModule::IncludeModule('iblock');

        $select = array_merge(array(
            'ID',
            'IBLOCK_ID',
            'NAME',
            'SORT',
            'ACTIVE',
            'PREVIEW_TEXT'
        ), $select);

        $dbRes = \CIBlockElement::GetList(array(), array(
            'IBLOCK_ID' => $block['iblock_id'],
            'ID' => $block['element_ids']
        ),false,false,$select);

        $unsorted = array();
        while ($aItem = $dbRes->GetNext()){
            $unsorted[ $aItem['ID'] ] = $aItem;
        }


        $result = array();
        foreach ($block['element_ids'] as $id){
            if (isset($unsorted[$id])){
                $result[] = $unsorted[$id];
            }
        }

        return $result;

    }

}