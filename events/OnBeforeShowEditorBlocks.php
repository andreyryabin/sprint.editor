<?php

use Sprint\Editor\AdminBlocks\ImageAdminBlock;

AddEventHandler(

    'sprint.editor',
    'OnBeforeShowEditorBlocks',
    function (&$blocks) {
        /**
         * Обработчик восстанавливает превьюшки в админке для блоков "Галерея" и "Картинка",
         * если они удалились из /upload/iblock/resize_cache/
         */
        foreach ($blocks as &$block) {
            if ($block['name'] == 'gallery') {
                foreach ($block['images'] as $key => $aItem) {
                    if (!empty($aItem['file']['ID'])) {
                        $aItem['file'] = Sprint\Editor\Tools\Image::resizeImage2(
                            $aItem['file']['ID'], [
                                'width'  => 98,
                                'height' => 55,
                                'exact'  => 1,
                            ]
                        );
                        $block['images'][$key] = $aItem;
                    }
                }
            } elseif ($block['name'] == 'video_gallery') {
                foreach ($block['items'] as $key => $aItem) {
                    if (!empty($aItem['file']['ID'])) {
                        $aItem['file'] = Sprint\Editor\Tools\Image::resizeImage2(
                            $aItem['file']['ID'], [
                                'width'  => 98,
                                'height' => 55,
                                'exact'  => 1,
                            ]
                        );
                        $block['items'][$key] = $aItem;
                    }
                }
            } elseif ($block['name'] == 'video') {
                if (!empty($block['preview']['file']['ID'])) {
                    $block['preview']['file'] = Sprint\Editor\Tools\Image::resizeImage2(
                        $block['preview']['file']['ID'],
                        [
                            'width'  => 200,
                            'height' => 200,
                            'exact'  => 1,
                        ]
                    );
                }
            } elseif ($block['name'] == 'image') {
                if (!empty($block['file']['ID'])) {
                    $block['file'] = Sprint\Editor\Tools\Image::resizeImage2(
                        $block['file']['ID'], [
                            'width'  => ImageAdminBlock::PREVIEW_WIDTH,
                            'height' => ImageAdminBlock::PREVIEW_HEIGHT,
                            'exact'  => ImageAdminBlock::PREVIEW_EXACT,
                        ]
                    );
                }
            } elseif ($block['name'] == 'complex_video_text') {
                if (isset($block['preview'])) {
                    $oldPrev = $block['preview'];
                    unset($block['preview']);
                    if (!isset($block['video']['preview'])) {
                        $block['video']['preview'] = $oldPrev;
                    }
                }
            }
        }
    }
);
