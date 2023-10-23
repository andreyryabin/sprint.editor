<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Coub as CoubTools;

/**
 * @deprecated use block video
 */
class Coub
{
    static public function getHtml($block, $params = [])
    {
        if (empty($block['url'])) {
            return '';
        }

        $block = array_merge(
            [
                'width'  => '420',
                'height' => '420',
            ], $block, $params
        );

        return CoubTools::getVideoHtml($block['url'], $block['width'], $block['height']);
    }
}
