<?php

namespace Sprint\Editor;

class Module
{
    protected static $modulename = 'sprint.editor';
    protected static $configcache = array();

    public static function getDocRoot() {
        return rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
    }

    public static function getPhpInterfaceDir() {
        if (is_dir(self::getDocRoot() . '/local/php_interface')) {
            return self::getDocRoot() . '/local/php_interface';
        } else {
            return self::getDocRoot() . '/bitrix/php_interface';
        }
    }

    public static function getModuleDir() {
        if (is_file(self::getDocRoot() . '/local/modules/'.self::$modulename.'/include.php')) {
            return self::getDocRoot() . '/local/modules/'.self::$modulename;
        } else {
            return self::getDocRoot() . '/bitrix/modules/'.self::$modulename;
        }
    }

    public static function getVersion() {
        $arModuleVersion = array();
        /** @noinspection PhpIncludeInspection */
        include self::getModuleDir() . '/install/version.php';
        return isset($arModuleVersion['VERSION']) ? $arModuleVersion['VERSION'] : '';
    }



    //options

    public static function getDbOption($name, $default=''){
        $val = \COption::GetOptionString(self::$modulename, $name, null);
        if (is_null($val)){
            $opts = self::getOptionsConfig();
            return isset($opts[$name]) ? $opts[$name]['DEFAULT'] : $default;
        }

        return $val;
    }

    public static function setDbOption($name, $value){
        if ($value != \COption::GetOptionString(self::$modulename, $name, '')) {
            \COption::SetOptionString(self::$modulename, $name, $value);
        }
    }

    public static function resetDbOptions(){
        $options = self::getOptionsConfig();
        foreach ($options as $name => $opt){
            \COption::RemoveOption(self::$modulename, $name);
        }
    }

    public static function getOptionsConfig(){
        if (empty(self::$configcache)){
            self::$configcache = include self::getModuleDir() . '/config.php';
        }
        return self::$configcache;
    }
}



