<?php

namespace Sprint\Editor\Blocks;

use CBXSanitizer;

class Text
{
    static public function getValue($block): string
    {
        if (empty($block['value'])) {
            return '';
        }

        $san = new CBXSanitizer();
        $san->SetLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);
        return $san->SanitizeHtml((string)$block['value']);
    }
}
