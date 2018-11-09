<?php
namespace Sprint\Editor\Blocks;
use Sprint\Editor\Tools\Image as ImageTools;
use Sprint\Editor\Tools\Youtube as YoutubeTools;

class VideoGallery
{

    static public function getItems($block, $resizePreview = array(),$resizeDetail = array()){
        if (empty($block['items'])){
            return array();
        }
        $resizePreview = array_merge(array(
            'width' => 200,
            'height' => 200,
            'exact' => 0,
        ), $resizePreview);

        $items = array();

        foreach ($block['items'] as $image){
            if (empty($image['file']['ID'])){
                continue;
            }

            $aItem = ImageTools::resizeImage2($image['file']['ID'],$resizePreview);
            $aItem['DETAIL_SRC'] = $aItem['SRC'];

            if (!empty($resizeDetail)){
                $aDetail = ImageTools::resizeImage2($image['file']['ID'],$resizeDetail);
                $aItem['DETAIL_SRC'] = $aDetail['SRC'];
            }

            $aItem['DESCRIPTION'] = htmlspecialcharsbx($image['desc']);
            $aItem['YOUTUBE_CODE'] = YoutubeTools::getVideoCode($image['video']);


            $items[] = $aItem;
        }

        return $items;
    }

}