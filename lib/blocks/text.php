<?php

namespace Sprint\Editor\Blocks;

use CBXSanitizer;

class Text
{
    static public function getValue($block): string
    {
        $value = trim($block['value'] ?? '');

        if (empty($value)) {
            return '';
        }


        $san = new CBXSanitizer();
        $san->SetLevel(CBXSanitizer::SECURE_LEVEL_LOW);

        $value = $san->SanitizeHtml($value);

        return str_replace(htmlspecialchars('&nbsp;'), '&nbsp;', $value);
    }
}
