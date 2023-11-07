<?php
/**
 * @var $blockName
 * @var $layouts
 */

$strings = [];

foreach ($layouts as $lindex => $layout) {
    if (!empty($layout['title'])) {
        $strings[] = '<div class="sp-x-box-caption">' . $layout['title'] . '</div>';
    }
    $strings[] = '<div class="sp-table sp-table-fixed">';
    $strings[] = '<div class="sp-row">';

    foreach ($layout['columns'] as $cindex => $column) {
        $strings[] = '<div class="sp-col">';
        foreach ($column['blocks'] as $cblock) {
            $strings[] = '<div class="sp-area ' . $cblock['areaclass'] . '"></div>';
        }
        $strings[] = '</div>';
    }

    $strings[] = ' </div>';
    $strings[] = ' </div>';
}

echo implode(PHP_EOL, $strings);
