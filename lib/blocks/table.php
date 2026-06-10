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

        $col['colspan'] = max(0, (int)($col['colspan'] ?? 0));
        $col['rowspan'] = max(0, (int)($col['rowspan'] ?? 0));
        $col['text'] = htmlspecialcharsbx((string)($col['text'] ?? ''));
        $col['class'] = htmlspecialcharsbx((string)($col['class'] ?? ''));
        $col['style'] = htmlspecialcharsbx($col['style']);

        return $col;
    }
}
