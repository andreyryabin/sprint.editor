<?php

AddEventHandler('sprint.editor', 'OnBeforeShowEditorBlocks', function (&$blocks) {
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


AddEventHandler('sprint.editor', 'OnBeforeShowComponentBlocks', function (&$blocks) {

    $htags = array();
    foreach ($blocks as $index => $block) {
        if ($block['name'] == 'htag') {

            $block['anchor'] = \CUtil::translit($block['value'], 'ru', array(
                "max_len" => 100,
                "change_case" => 'L',
                "replace_space" => '-',
                "replace_other" => '-',
                "delete_repeat_replace" => true,
            ));

            $blocks[$index] = $block;
            $htags[] = $block;
        }
    }

    foreach ($blocks as $index => $block) {
        if ($block['name'] == 'contents') {
            $block['elements'] = array();

            sort($block['selectors']);
            $selectors = array_flip($block['selectors']);

            foreach ($htags as $htag) {
                $type = $htag['type'];
                if (isset($selectors[$type])) {
                    $block['elements'][] = array(
                        'text' => $htag['value'],
                        'anchor' => $htag['anchor'],
                        'level' => $selectors[$type] + 1,
                    );
                }
            }

            $blocks[$index] = $block;
        }
    }

});