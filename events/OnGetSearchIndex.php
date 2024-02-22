<?php

AddEventHandler(
    'sprint.editor',
    'OnGetSearchIndex',
    function ($value, $search) {
        foreach ($value['blocks'] as $block) {
            if ($block['name'] == 'text' && !empty($block['value'])) {
                $search .= ' ' . $block['value'];
            }
            if ($block['name'] == 'htag' && !empty($block['value'])) {
                $search .= ' ' . $block['value'];
            }
            if ($block['name'] == 'lists') {
                foreach ($block['elements'] as $elem) {
                    $search .= ' ' . $elem['text'];
                }
            }
            if ($block['name'] == 'accordion' && !empty($block['items'])) {
                foreach ($block['items'] as $accordionTab) {
                    $search .= ' ' . $accordionTab['title'];
                    foreach ($accordionTab['blocks'] as $accordionTabBlock) {
                        if ($accordionTabBlock['name'] == 'text' && !empty($accordionTabBlock['value'])) {
                            $search .= ' ' . $accordionTabBlock['value'];
                        }
                        if ($accordionTabBlock['name'] == 'htag' && !empty($accordionTabBlock['value'])) {
                            $search .= ' ' . $block['value'];
                        }
                    }
                }
            }
        }
        return $search;
    }
);
