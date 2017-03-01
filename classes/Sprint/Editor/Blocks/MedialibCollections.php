<?php
namespace Sprint\Editor\Blocks;
use Sprint\Editor\Tools\Medialib;

class MedialibCollections
{

    static public function getImages($block, $resizeParams = array()){
        if (empty($block['collections'])){
            return array();
        }

        $result = Medialib::GetElements(array(
            'collection_id' => $block['collections']
        ), array(), $resizeParams);

        return $result['items'];
    }

}