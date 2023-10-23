<?php

namespace Sprint\Editor\Tools;

class Vimeo
{
    static public function getVideoCode($url, $default = '')
    {
        $matches = [];
        if (preg_match(
            '%(https?://)?(www\.)?(player\.)?vimeo\.com/([a-z]*/)*([0-9]{6,11})[?]?.*%',
            $url,
            $matches
        )) {
            return $matches[5];
        }
        return $default;
    }

    static public function getPreviewImg($url)
    {
        return '';
    }

    static public function getVideoHtml($url, $width = '560', $height = '315')
    {
        $code = self::getVideoCode($url);
        if ($code) {
            return sprintf(
                '<iframe width="%s" height="%s" src="https://player.vimeo.com/video/%s" allowfullscreen></iframe>',
                $width,
                $height,
                $code
            );
        }
        return '';
    }
}
