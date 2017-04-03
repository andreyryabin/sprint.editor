<?php

AddEventHandler('sprint.editor', 'OnBeforeShowComponentBlocks', function (&$blocks) {
    /**
     * Обработчик блока "Содержание", проставляет блокам "Заголовок" дополнительное поле якорь (anchor),
     * строит содержание (elements) ссылающееся на эти якоря
     */

    $contentsBlockFound = false;
    foreach ($blocks as $index => $block) {
        if ($block['name'] == 'contents') {
            $contentsBlockFound = true;
            break;
        }
    }

    if (!$contentsBlockFound) {
        return false;
    }

    $htagBlocks = array();
    foreach ($blocks as $index => $block) {
        if ($block['name'] == 'htag') {
            $block['anchor'] = \CUtil::translit($block['value'], 'ru', array(
                "max_len" => 100,
                "change_case" => 'L',
                "replace_space" => '-',
                "replace_other" => '-',
                "delete_repeat_replace" => true,
            ));

            $htagBlocks[] = $block;
            $blocks[$index] = $block;
        }
    }

    foreach ($blocks as $index => $block) {
        if ($block['name'] == 'contents') {
            sort($block['selectors']);
            $levels = array_flip($block['selectors']);

            $block['elements'] = array();
            foreach ($htagBlocks as $htagBlock) {
                $type = $htagBlock['type'];
                if (isset($levels[$type])) {
                    $block['elements'][] = array(
                        'text' => $htagBlock['value'],
                        'anchor' => $htagBlock['anchor'],
                        'level' => $levels[$type] + 1,
                    );
                }
            }

            $blocks[$index] = $block;
        }
    }

    return true;

});