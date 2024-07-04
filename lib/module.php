<?php

namespace Sprint\Editor;

use COption;

class Module
{
    const ID = 'sprint.editor';
    protected static $configcache = [];

    public static function getDocRoot(): string
    {
        return rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
    }

    public static function getModuleDir(): string
    {
        if (is_file(self::getDocRoot() . '/local/modules/' . Module::ID . '/include.php')) {
            return self::getDocRoot() . '/local/modules/' . Module::ID;
        } else {
            return self::getDocRoot() . '/bitrix/modules/' . Module::ID;
        }
    }

    public static function getAdminDir(): string
    {
        if (is_dir(self::getDocRoot() . '/local/admin/sprint.editor/')) {
            return self::getDocRoot() . '/local/admin/sprint.editor/';
        } else {
            return self::getDocRoot() . '/bitrix/admin/sprint.editor/';
        }
    }

    public static function getAdminSubDir($subdir): string
    {
        if (is_dir(self::getDocRoot() . '/local/admin/sprint.editor/' . $subdir . '/')) {
            return self::getDocRoot() . '/local/admin/sprint.editor/' . $subdir . '/';
        } else {
            return self::getDocRoot() . '/bitrix/admin/sprint.editor/' . $subdir . '/';
        }
    }

    public static function getSettingsDir(): string
    {
        return self::getAdminSubDir('settings');
    }

    public static function getPacksDir(): string
    {
        return self::getAdminSubDir('packs');
    }

    public static function getSnippetsDir(): string
    {
        return self::getAdminSubDir('snippets');
    }

    public static function getVersion()
    {
        $arModuleVersion = [];
        include self::getModuleDir() . '/install/version.php';
        return $arModuleVersion['VERSION'] ?? '';
    }

    //options

    public static function getDbOption($name, $default = '')
    {
        $val = COption::GetOptionString(Module::ID, $name, null);
        if (is_null($val)) {
            $opts = self::getOptionsConfig();
            return isset($opts[$name]) ? $opts[$name]['DEFAULT'] : $default;
        }

        return $val;
    }

    public static function setDbOption($name, $value)
    {
        if ($value != COption::GetOptionString(Module::ID, $name, '')) {
            COption::SetOptionString(Module::ID, $name, $value);
        }
    }

    public static function resetDbOptions()
    {
        $options = self::getOptionsConfig();
        foreach ($options as $name => $opt) {
            COption::RemoveOption(Module::ID, $name);
        }
    }

    public static function getOptionsConfig()
    {
        if (empty(self::$configcache)) {
            self::$configcache = include self::getModuleDir() . '/config.php';
        }
        return self::$configcache;
    }

    protected static function makeDir($dir): string
    {
        $dir = self::getDocRoot() . $dir;
        if (!is_dir($dir)) {
            mkdir($dir, BX_DIR_PERMISSIONS, true);
        }

        return $dir;
    }

    public static function templater($file, $vars = [])
    {
        if (is_array($vars)) {
            extract($vars, EXTR_SKIP);
        }

        ob_start();
        include self::getModuleDir() . $file;

        return ob_get_clean();
    }
}



