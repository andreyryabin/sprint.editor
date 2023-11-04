<?php

namespace Sprint\Editor\Blocks;

use SprintEditorBlocksComponent;

class Contents
{
    public static function getElements($block, SprintEditorBlocksComponent $component): array
    {
        $levels = ['h1' => 0];
        if (isset($block['selectors']) && is_array($block['selectors'])) {
            sort($block['selectors']);
            $levels = array_flip($block['selectors']);
        }

        $elements = [];
        foreach ($component->getLayoutsBlocks() as $blocks) {
            foreach ($blocks as $htagblock) {
                if ($htagblock['name'] == 'htag') {
                    $type = $htagblock['type'];
                    if (isset($levels[$type])) {
                        $elements[] = [
                            'text'   => $htagblock['value'],
                            'anchor' => $htagblock['anchor'],
                            'level'  => $levels[$type] + 1,
                        ];
                    }
                }
            }
        }

        return $elements;
    }
}
