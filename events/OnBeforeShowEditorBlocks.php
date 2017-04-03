<?php

AddEventHandler('sprint.editor', 'OnBeforeShowEditorBlocks', function (&$blocks) {
    /**
     * Обработчик восстанавливает превьюшки в админке для блоков "Галерея" и "Картинка",
     * если они удалились из /upload/iblock/resize_cache/
     */
    foreach ($blocks as $block) {
        if ($block['name'] == 'gallery') {
            foreach ($block['images'] as &$aItem) {
                if (!empty($aItem['file']['ID'])) {
                    $aItem['file'] = Sprint\Editor\Tools\Image::resizeImageById(
                        $aItem['file']['ID'], 100, 100, 1
                    );
                }
            }
        } elseif ($block['name'] == 'video') {
            if (!empty($block['preview']['file']['ID'])) {
                $block['preview']['file'] = Sprint\Editor\Tools\Image::resizeImageById(
                    $block['preview']['file']['ID'], 200, 200, 1
                );
            }
        } elseif ($block['name'] == 'image') {
            if (!empty($block['file']['ID'])) {
                $block['file'] = Sprint\Editor\Tools\Image::resizeImageById(
                    $block['file']['ID'], 200, 200, 1
                );
            }
        }
    }
});
