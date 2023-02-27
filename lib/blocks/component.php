<?php

namespace Sprint\Editor\Blocks;

class Component
{
    static public function initializeParams($block)
    {
        foreach ($block['component_params'] as $paramKey => $paramVal) {
            if (self::hasExpression($paramVal)) {
                if (self::hasArrayExpression($paramVal)) {
                    $paramVal = self::calcArrayExpression($paramVal);
                } elseif (self::hasRequestExpression($paramVal)) {
                    $paramVal = self::calcRequestExpression($paramVal);
                } else {
                    $paramVal = self::calcExpression($paramVal);
                }
                $block['component_params'][$paramKey] = $paramVal;
            }
        }

        return $block;
    }

    static protected function hasArrayExpression($str)
    {
        return strpos($str, '={array(') === 0 && substr($str, -2, 2) == ')}';
    }

    static protected function hasRequestExpression($str)
    {
        return strpos($str, '={$_REQUEST[') === 0 && substr($str, -2, 2) == ']}';
    }

    static protected function hasRequestGetExpression($str)
    {
        return strpos($str, '={$_GET[') === 0 && substr($str, -2, 2) == ']}';
    }

    static protected function hasExpression($str)
    {
        return (substr($str, 0, 2) == "={" && substr($str, -1, 1) == "}" && strlen($str) > 3);
    }

    static protected function calcArrayExpression($str)
    {
        $str = substr($str, 8, -2);
        //$str = explode(',', $str);
        $str = explode('",', $str);
        foreach ($str as $key => $val) {
            $str[$key] = trim($val, ' \'"');
        }
        return $str;
    }

    static protected function calcRequestExpression($str)
    {
        $str = substr($str, 12, -2);
        $str = trim($str, ' \'"');
        $str = isset($_REQUEST[$str]) ? $_REQUEST[$str] : '';
        return $str;
    }

    static protected function calcRequestGetExpression($str)
    {
        $str = substr($str, 8, -2);
        $str = trim($str, ' \'"');
        $str = isset($_GET[$str]) ? $_GET[$str] : '';
        return $str;
    }

    static protected function calcExpression($str)
    {
        $str = substr($str, 2, -1);
        $str = eval('return ' . $str . ';');
        return $str;
    }
}
