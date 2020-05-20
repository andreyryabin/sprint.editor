<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Image as ImageTools;

class Gallery
{
    static public function getImages($block, $resizePreview = [], $resizeDetail = [])
    {
        if (empty($block['images'])) {
            return [];
        }
        $resizePreview = array_merge(
            [
                'width'  => 200,
                'height' => 200,
                'exact'  => 0,
            ], $resizePreview
        );

        $items = [];

        foreach ($block['images'] as $image) {
            if (empty($image['file']['ID'])) {
                continue;
            }

            $aItem = ImageTools::resizeImage2($image['file']['ID'], $resizePreview);
            $aItem['DETAIL_SRC'] = $aItem['SRC'];

            if (!empty($resizeDetail)) {
                $aDetail = ImageTools::resizeImage2($image['file']['ID'], $resizeDetail);
                $aItem['DETAIL_SRC'] = $aDetail['SRC'];
            }

            $aItem['DESCRIPTION'] = htmlspecialcharsbx($image['desc']);

            $items[] = $aItem;
        }

        return $items;
    }
}
