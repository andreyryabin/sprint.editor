<?php
namespace Sprint\Editor\Blocks;
use Sprint\Editor\Tools\Medialib;

class MedialibElements
{

    static public function getImages($block, $resizeParams = array()){
        if (empty($block['element_ids']) || empty($block['collection_id'])){
            return array();
        }

        $dbresult = Medialib::GetElements(array(
            'collection_id' => $block['collection_id'],
            'id' => $block['element_ids']
        ), array(), $resizeParams);


        $unsorted = array();
        foreach ($dbresult['items'] as $aItem) {
            $unsorted[ $aItem['ID'] ] = $aItem;
        }

        $elements = array();
        foreach ($block['element_ids'] as $id){
            if (isset($unsorted[$id])){
                $elements[] = $unsorted[$id];
            }
        }

        return $elements;
    }

}