<?php

namespace Sprint\Editor\Blocks;

class Table
{
    public static function prepareColumn($col)
    {
        $col['style'] = '';
        if (!empty($col['attrs']) && is_array($col['attrs'])) {
            foreach ($col['attrs'] as $attr) {
                if ($attr == 'center' || $attr == 'right') {
                    $col['style'] .= 'text-align:' . $attr . ';';
                } elseif ($attr == 'bold') {
                    $col['style'] .= 'font-weight:bold;';
                }
            }
        }

        $col['colspan'] = isset($col['colspan']) ? intval($col['colspan']) : 0;
        if ($col['colspan'] <= 1) {
            $col['colspan'] = 0;
        }

        $col['rowspan'] = isset($col['rowspan']) ? intval($col['rowspan']) : 0;
        if ($col['rowspan'] <= 1) {
            $col['rowspan'] = 0;
        }

        $col['text'] = isset($col['text']) ? trim($col['text']) : '';

        return $col;
    }
}
