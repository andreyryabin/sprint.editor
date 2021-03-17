<?php

namespace Sprint\Editor\Blocks;

class Lists
{
    static public function getValue($block, $tag = 'ul', $attrs = '')
    {
        $html = '';
        if (!empty($block['elements'])) {
            $html = '<' . $tag . ($attrs ? ' ' . $attrs : '') . '>';
            foreach ($block['elements'] as $item) {
                $html .= '<li>' . $item['text'] . self::getValue($item, $tag, $attrs) . '</li>';
            }
            $html .= '</' . $tag . '>';
        }
        return $html;
    }
}
