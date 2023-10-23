<?php

namespace Sprint\Editor\Tools;

class UploadMp4
{
    static public function getVideoCode($url, $default = '')
    {
        if (preg_match(
            '%^(/upload/.+\.mp4)$%i',
            $url
        )) {
            return $url;
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
            $html = '<video width="%s" height="%s" controls="controls">';
            $html .= '<source src="%s" type="video/mp4">';
            $html .= 'Тег video не поддерживается вашим браузером.';
            $html .= '</video>';

            return sprintf(
                $html,
                $width,
                $height,
                $code
            );
        }
        return '';
    }
}
