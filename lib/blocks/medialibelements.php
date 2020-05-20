<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Tools\Medialib;

class MedialibElements
{
    static public function getImages($block, $resizePreview = [], $resizeDetail = [])
    {
        if (empty($block['element_ids']) || empty($block['collection_id'])) {
            return [];
        }

        $dbresult = Medialib::GetElements(
            [
                'collection_id' => $block['collection_id'],
                'id'            => $block['element_ids'],
            ], [], $resizePreview, $resizeDetail
        );

        $unsorted = [];
        foreach ($dbresult['items'] as $aItem) {
            $unsorted[$aItem['ID']] = $aItem;
        }

        $elements = [];
        foreach ($block['element_ids'] as $id) {
            if (isset($unsorted[$id])) {
                $elements[] = $unsorted[$id];
            }
        }

        return $elements;
    }
}
