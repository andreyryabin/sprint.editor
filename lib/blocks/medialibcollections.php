<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Medialib;

class MedialibCollections
{
    static public function getImages($block, $resizePreview = [], $resizeDetail = [])
    {
        if (empty($block['collections'])) {
            return [];
        }

        $result = Medialib::GetElements(
            [
                'collection_id' => $block['collections'],
            ], [], $resizePreview, $resizeDetail
        );

        return $result['items'];
    }
}
