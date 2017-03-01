<?php
namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Youtube;

class Video
{

    static public function getHtml($block, $params = array()){
        if (empty($block['url'])){
            return '';
        }

        $params = array_merge(array(
            'width' => '560',
            'height' => '315',
        ), $params);

        return Youtube::getVideoHtml($block['url'], $params['width'], $params['height']);

    }
}