<?php
namespace Sprint\Editor\Blocks;
use Sprint\Editor\Tools\Image as ImageTools;

class Image
{

    static public function getImage($block, $resizeParams = array()){
        if (empty($block['file'])){
            return array();
        }
        $resizeParams = array_merge(array(
            'width' => 1024,
            'height' => 768,
            'exact' => 0,
        ), $resizeParams);

        $aItem = ImageTools::resizeImageById(
            $block['file']['ID'],
            $resizeParams['width'],
            $resizeParams['height'],
            $resizeParams['exact']
        );

        $aItem['DESCRIPTION'] = htmlspecialchars($block['desc']);
        return $aItem;
    }

}