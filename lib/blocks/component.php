<?php

namespace Sprint\Editor\Blocks;

class Component
{

    /**
     * Сохраняем совместимость с php7.4
     * @return array|false
     */
    public static function initialize(array $block)
    {
        if (empty($block['component_name'])) {
            return false;
        }

        if (!preg_match('#^[a-z0-9_.:-]+$#i', (string)$block['component_name'])) {
            return false;
        }

        if (empty($block['component_params']) || !is_array($block['component_params'])) {
            return false;
        }

        return static::initializeParams($block);
    }

    static public function initializeParams($block)
    {
        foreach ($block['component_params'] as $paramKey => $paramVal) {
            if (self::hasExpression($paramVal)) {
                if (self::hasArrayExpression($paramVal)) {
                    $paramVal = self::calcArrayExpression($paramVal);
                } elseif (self::hasRequestExpression($paramVal)) {
                    $paramVal = self::calcRequestExpression($paramVal);
                } elseif (self::hasGetExpression($paramVal)) {
                    $paramVal = self::calcGetExpression($paramVal);
                } else {
                    $paramVal = '';
                }
                $block['component_params'][$paramKey] = $paramVal;
            }
        }

        return $block;
    }

    static protected function hasArrayExpression($str)
    {
        return str_starts_with($str, '={array(') && str_ends_with($str, ')}');
    }

    static protected function hasRequestExpression($str)
    {
        return str_starts_with($str, '={$_REQUEST[') && str_ends_with($str, ']}');
    }

    static protected function hasGetExpression($str)
    {
        return str_starts_with($str, '={$_GET[') && str_ends_with($str, ']}');
    }

    static protected function hasExpression($str)
    {
        return (str_starts_with($str, "={") && str_ends_with($str, "}") && strlen($str) > 3);
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
        $str = $_REQUEST[$str] ?? '';

        if (is_array($str)) {
            $str = array_map('htmlspecialcharsbx', $str);
        } else {
            $str = htmlspecialcharsbx((string)$str);
        }

        return $str;
    }

    static protected function calcGetExpression($str)
    {
        $str = substr($str, 8, -2);
        $str = trim($str, ' \'"');
        $str = $_GET[$str] ?? '';

        if (is_array($str)) {
            $str = array_map('htmlspecialcharsbx', $str);
        } else {
            $str = htmlspecialcharsbx((string)$str);
        }

        return $str;
    }


}
