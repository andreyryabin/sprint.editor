<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Vimeo;
use Sprint\Editor\Tools\Youtube;

class Video
{
    static public function getHtml($block, $params = [])
    {
        if (empty($block['url'])) {
            return '';
        }
        $block = array_merge(
            [
                'width'  => '100%',
                'height' => 480,
            ], $block, $params
        );

        $services = [
            'youtube' => Youtube::class,
            'vimeo'   => Vimeo::class,
        ];

        $videoHtml = '';
        foreach ($services as $code => $service) {
            $videoHtml = $service::getVideoHtml($block['url'], $block['width'], $block['height']);
            if ($videoHtml) {
                break;
            }
        }

        return $videoHtml;
    }
}
