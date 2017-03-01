<?php
namespace Sprint\Editor\Blocks;

class Slideshare
{

    static public function getHtml($block, $params = array()){
        if (empty($block['embed_url'])){
            return '';
        }

        $params = array_merge(array(
            'width' => '510',
            'height' => '420',
        ), $params);


        return sprintf('<iframe src="%s" width="%s" height="%s" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen> </iframe>',
            $block['embed_url'],
            $params['width'],
            $params['height']
        );


    }
}