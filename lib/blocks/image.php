<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Image as ImageTools;

class Image
{
    static public function getImage($block, $resizeParams = [], $resizeDetail = [])
    {
        if (empty($block['file'])) {
            return [];
        }

        $aItem = ImageTools::resizeImage2($block['file']['ID'], $resizeParams);

        if (empty($aItem)) {
            return [];
        }

        $aItem['DESCRIPTION'] = htmlspecialcharsbx($block['desc']);

        if (!empty($resizeDetail)) {
            $aDetail = ImageTools::resizeImage2($block['file']['ID'], $resizeDetail);
            $aItem['DETAIL_SRC'] = $aDetail['SRC'];
        }

        return $aItem;
    }
}
