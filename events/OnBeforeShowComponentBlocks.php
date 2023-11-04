<?php

AddEventHandler(
    'sprint.editor',
    'OnBeforeShowComponentBlocks',
    function (&$blocks) {
        foreach ($blocks as $index => $block) {
            $hidden = $block['meta']['hidden'] ?? false;
            if ($hidden) {
                unset($blocks[$index]);
            }
        }
    }
);

