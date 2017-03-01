<?php
namespace Sprint\Editor\Blocks;

class Coub
{

    static public function getHtml($block, $params = array()){
        if (empty($block['url'])){
            return '';
        }

        $block = array_merge(array(
            'width' => '420',
            'height' => '420',
        ), $block);


        $matches = array();
        if (preg_match('/^.*coub.com\/view\/(\w+)/',$block['url'], $matches)){
            return sprintf('<iframe src="//coub.com/embed/%s?muted=false&autostart=false&originalSize=false&startWithHD=false" allowfullscreen="true" frameborder="0" width="%s" height="%s"></iframe>',
                $matches[1],
                $block['width'],
                $block['height']
            );
        } else {
            return '';
        }
    }
}