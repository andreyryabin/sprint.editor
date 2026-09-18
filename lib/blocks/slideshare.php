<?php

namespace Sprint\Editor\Blocks;

class Slideshare
{
    static public function getHtml($block, $params = [])
    {
        if (empty($block['embed_url'])) {
            return '';
        }

        $embedUrl = (string)$block['embed_url'];

        //разрешаем только ссылки slideshare, без символов, ломающих HTML-атрибут
        if (!preg_match('#^https://(www\.)?slideshare\.net/[^"\'<>]*$#i', $embedUrl)) {
            return '';
        }

        $block = array_merge(
            [
                'width'  => '510',
                'height' => '420',
            ], $block, $params
        );

        return sprintf(
            '<iframe src="%s" width="%d" height="%d" allowfullscreen> </iframe>',
            htmlspecialcharsbx($embedUrl),
            (int)$block['width'],
            (int)$block['height']
        );
    }
}
