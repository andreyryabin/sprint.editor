<?php
namespace Sprint\Editor\Blocks;

class Text
{

    static public function getValue($block){
        if (empty($block['value'])){
            return '';
        }

        return $block['value'];
    }

}