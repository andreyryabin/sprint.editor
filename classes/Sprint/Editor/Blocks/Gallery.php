<?php
namespace Sprint\Editor\Blocks;
use Sprint\Editor\Tools\Image as ImageTools;

class Gallery
{

    static public function getImages($block, $resizeParams = array()){
        if (empty($block['images'])){
            return array();
        }
        $resizeParams = array_merge(array(
            'width' => 200,
            'height' => 200,
            'exact' => 0,
        ), $resizeParams);

        $items = array();

        foreach ($block['images'] as $image){
            if (empty($image['file']['ID'])){
                continue;
            }

            $aItem = ImageTools::resizeImageById(
                $image['file']['ID'],
                $resizeParams['width'],
                $resizeParams['height'],
                $resizeParams['exact']
            );

            $aItem['DESCRIPTION'] = htmlspecialchars($image['desc']);

            $items[] = $aItem;
        }

        return $items;
    }

}