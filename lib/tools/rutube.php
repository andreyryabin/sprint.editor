<?php

namespace Sprint\Editor\Tools;

class Rutube
{
    static public function getVideoCode($url, $default = '')
    {
        $matches = [];
        if (preg_match(
            '%rutube.ru/video/([a-z0-9]+)/%i',
            $url,
            $matches
        )) {
            return $matches[1];
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
                '<iframe width="%s" height="%s" src="https://rutube.ru/pl/?pl_video=%s" allowfullscreen></iframe>',
                    $width,
                    $height,
                    $code
            );
        }
        return '';
    }
}
