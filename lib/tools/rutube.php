<?php

namespace Sprint\Editor\Tools;

class Rutube
{
    static public function getVideoCode($url, $default = '')
    {
        $matches = [];
        if (preg_match(
            '%rutube.ru\/video\/(\w+)\/%i',
            $url,
            $matches
        )) {
            return $matches[1];
        }
        return $default;
    }

    static public function getPreviewImg($url)
    {
        $code = self::getVideoCode($url);
        if ($code) {
            return sprintf('http://img.youtube.com/vi/%s/0.jpg', $code);
        }
        return '';
    }

    static public function getVideoHtml($url, $width = '560', $height = '315')
    {
        $code = self::getVideoCode($url);
        if ($code) {
            return sprintf(
                '<iframe width="%s" height="%s" src="https://rutube.ru/play/embed/%s" frameborder="0" frameBorder="0" allow="clipboard-write" mozallowfullscreen webkitAllowFullScreen allowfullscreen></iframe>',
                    $width,
                    $height,
                    $code
            );
        }
        return '';
    }
}
