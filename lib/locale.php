<?php

namespace Sprint\Editor;

class Locale
{
    private static $localeLoaded = false;
    private static $messages     = [];

    public static function isWin1251()
    {
        return (defined('BX_UTF') && BX_UTF === true) ? 0 : 1;
    }

    public static function convertToWin1251IfNeed($msg)
    {
        if (self::isWin1251()) {
            if (self::detectUtf8($msg)) {
                $msg = self::deepConvert('utf-8', 'windows-1251//IGNORE', $msg);
            }
        }
        return $msg;
    }

    public static function convertToUtf8IfNeed($msg)
    {
        if (self::isWin1251()) {
            if (!self::detectUtf8($msg)) {
                $msg = self::deepConvert('windows-1251', 'utf-8//IGNORE', $msg);
            }
        }
        return $msg;
    }

    protected static function detectUtf8($msg)
    {
        $msg = is_array($msg) ? serialize($msg) : $msg;
        return (md5($msg) == md5(iconv('utf-8', 'utf-8', $msg))) ? 1 : 0;
    }

    public static function loadLocale($loc)
    {
        global $MESS;

        if (!self::$localeLoaded) {
            foreach ($loc as $key => $msg) {
                $msg = self::convertToWin1251IfNeed($msg);
                self::$messages[$key] = $msg;
                $MESS[$key] = $msg;
            }
        }
    }

    public static function GetLangMessages()
    {
        return self::$messages;
    }

    public static function truncateText($strText, $intLen = 60)
    {
        $strText = strip_tags($strText);
        if (self::isWin1251()) {
            if (strlen($strText) > $intLen) {
                return rtrim(substr($strText, 0, $intLen), ".") . "...";
            } else {
                return $strText;
            }
        } else {
            if (mb_strlen($strText, 'UTF-8') > $intLen) {
                return rtrim(mb_substr($strText, 0, $intLen, 'UTF-8'), ".") . "...";
            } else {
                return $strText;
            }
        }
    }

    protected static function deepConvert($from, $to, $target)
    {
        if (is_array($target)) {
            foreach ($target as &$val) {
                $val = self::deepConvert($from, $to, $val);
            }
            return $target;
        } else {
            return iconv($from, $to, $target);
        }
    }
}
