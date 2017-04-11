<?php

AddEventHandler('sprint.editor', 'OnBeforeShowComponentBlocks', function (&$blocks) {
    /**
     * Обработчик блока "Содержание", проставляет блокам "Заголовок" дополнительное поле якорь (anchor),
     * строит содержание (elements) ссылающееся на эти якоря
     */
    foreach ($blocks as $index => $block) {
        if ($block['name'] == 'contents') {

            sort($block['selectors']);
            $levels = array_flip($block['selectors']);

            $block['elements'] = array();
            foreach ($blocks as $htagblock) {
                if ($htagblock['name'] == 'htag') {
                    $type = $htagblock['type'];
                    if (isset($levels[$type])) {
                        $block['elements'][] = array(
                            'text' => $htagblock['value'],
                            'anchor' => $htagblock['anchor'],
                            'level' => $levels[$type] + 1,
                        );
                    }
                }
            }

            $blocks[$index] = $block;

        }
    }

});