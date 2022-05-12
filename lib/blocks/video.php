<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Rutube;
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
            Youtube::class,
            Vimeo::class,
            Rutube::class,
        ];

        foreach ($services as $service) {
            $videoHtml = $service::getVideoHtml($block['url'], $block['width'], $block['height']);
            if ($videoHtml) {
                break;
            }
        }

        return $videoHtml;
    }
}
