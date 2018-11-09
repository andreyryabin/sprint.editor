<?php
namespace Sprint\Editor\Blocks;
use Sprint\Editor\Tools\Image as ImageTools;

class Image
{

    static public function getImage($block, $resizeParams = array(), $resizeDetail = array()){
        if (empty($block['file'])){
            return array();
        }
        $resizeParams = array_merge(array(
            'width' => 1024,
            'height' => 768,
            'exact' => 0,
        ), $resizeParams);

        $aItem = ImageTools::resizeImage2($block['file']['ID'],$resizeParams);

        $aItem['DESCRIPTION'] = htmlspecialcharsbx($block['desc']);

        if (!empty($resizeDetail)) {
            $aDetail = ImageTools::resizeImage2($block['file']['ID'], $resizeDetail);
            $aItem['DETAIL_SRC'] = $aDetail['SRC'];
        }

        return $aItem;
    }

}