<?php

namespace Sprint\Editor\Tools;

class Coub
{
    static public function getVideoCode($url, $default = '')
    {
        $matches = [];
        if (preg_match(
            '%^.*coub.com/view/(\w+)%i',
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
                '<iframe src="//coub.com/embed/%s?muted=false&autostart=false&originalSize=false&startWithHD=false" allowfullscreen width="%s" height="%s"></iframe>',
                $code,
                $width,
                $height
            );
        }
        return '';
    }
}
