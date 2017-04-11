<?php
namespace Sprint\Editor\Blocks;

class Component
{

    static public function initializeParams($block){
        foreach ($block['component_params'] as $paramKey => $paramVal){
            if (self::hasBrackets($paramVal)){
                $paramVal = self::trimBrackets($paramVal);
                $paramVal = eval('return '. $paramVal . ';');
                $block['component_params'][$paramKey] = $paramVal;
            }
        }
        return $block;
    }

    static protected function hasBrackets($str){
        return (substr($str, 0, 2) == "={" && substr($str, -1, 1)=="}" && strlen($str)>3);
    }

    static protected function trimBrackets($str){
        return substr($str, 2, -1);
    }
}