<?php
namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Youtube;

class Video
{

    static public function getHtml($block, $params = array()){
        if (empty($block['url'])){
            return '';
        }

        $block = array_merge(array(
            'width' => '100%',
            'height' => 480
        ), $block, $params);

        return Youtube::getVideoHtml($block['url'], $block['width'], $block['height']);

    }
}