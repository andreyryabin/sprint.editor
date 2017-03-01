<?php
namespace Sprint\Editor\Tools;

class Youtube
{

    static public function getVideoCode($youtubeLink, $default = '') {
        $matches = array();
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtubeLink, $matches)) {
            return $matches[1];
        }
        return $default;
    }

    static public function getPreviewImg($videoUrl) {
        $code = self::getVideoCode($videoUrl);
        if ($code){
            return sprintf('http://img.youtube.com/vi/%s/0.jpg', $code);
        }
        return '';
    }

    static public function getVideoHtml($youtubeLink, $width = '560', $height = '315'){
        $code = self::getVideoCode($youtubeLink);
        if ($code){
            return sprintf('<iframe width="%s" height="%s" src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>', $width, $height, $code);
        }

        return '';
    }
}