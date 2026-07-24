<?php

namespace Sprint\Editor\Blocks;

use CBXSanitizer;

class Table
{
    public static function prepareColumn($col, $sanitize = true)
    {
        $col['colspan'] = max(0, (int)($col['colspan'] ?? 0));
        $col['rowspan'] = max(0, (int)($col['rowspan'] ?? 0));
        $col['class'] = (string)($col['class'] ?? '');
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

        if ($sanitize) {
            $col['class'] = htmlspecialcharsbx($col['class']);
            $col['style'] = htmlspecialcharsbx($col['style']);

            $san = new CBXSanitizer();
            $san->SetLevel(CBXSanitizer::SECURE_LEVEL_LOW);

            $col['text'] = $san->SanitizeHtml($col['text']);
        }


        return $col;
    }
}
