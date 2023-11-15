<?php

namespace Sprint\Editor;

use CUtil;
use DirectoryIterator;
use Sprint\Editor\Exceptions\AdminPageException;

class ComplexBuilder
{
    protected static array $allblocks = [];

    public static function init()
    {
        self::registerBlocks('blocks', false, false);
        self::registerBlocks('blocks', true, false);

        self::registerBlocks('my', false, true);
        self::registerBlocks('my', true, true);

        self::registerBlocks('complex', false, true);
        self::registerBlocks('complex', true, true);

        self::registerAssets();
    }

    public static function getComplexBlocks()
    {
        $filteredBlocks = array_filter(
            self::$allblocks,
            function ($block) {
                return ($block['groupname'] == 'complex' && $block['iscomplex']);
            }
        );

        return self::sortByNum($filteredBlocks, 'sort');
    }

    public static function getBlocksToolbar()
    {
        $blocksToolbar = [];
        foreach (['blocks', 'my'] as $groupname) {
            $filteredBlocks = array_filter(
                self::$allblocks,
                function ($block) use ($groupname) {
                    return ($block['groupname'] == $groupname);
                }
            );
            $blocksToolbar[] = [
                'title'  => GetMessage('SPRINT_EDITOR_group_' . $groupname),
                'blocks' => self::sortByNum($filteredBlocks, 'sort'),
            ];
        }

        return array_values(
            array_filter(
                $blocksToolbar,
                function ($toolbarItem) {
                    return !empty($toolbarItem['blocks']);
                }
            )
        );
    }

    protected static function registerAssets()
    {
        global $APPLICATION;

        CUtil::InitJSCore(["jquery"]);
        CUtil::InitJSCore(['translit']);

        if (Module::getDbOption('load_jquery_ui') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/jquery-ui-1.13.2.custom/jquery-ui.js');
        }

        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/complex_builder.js');
    }

    protected static function getGroupPath($groupname, $islocal)
    {
        if ($islocal) {
            return Module::getDocRoot() . '/local/admin/sprint.editor/' . $groupname . '/';
        }
        return Module::getDocRoot() . '/bitrix/admin/sprint.editor/' . $groupname . '/';
    }

    protected static function registerBlocks($groupname, $islocal, $checkname)
    {
        $rootpath = self::getGroupPath($groupname, $islocal);

        if (!is_dir($rootpath)) {
            return false;
        }

        $iterator = new DirectoryIterator($rootpath);
        foreach ($iterator as $item) {
            if (!$item->isDir() || $item->isDot()) {
                continue;
            }

            $blockId = $item->getFilename();

            if ($checkname) {
                if (strpos($blockId, $groupname) !== 0) {
                    continue;
                }
            }

            $param = [];
            if (is_file($rootpath . $blockId . '/config.json')) {
                $param = file_get_contents($rootpath . $blockId . '/config.json');
                $param = json_decode($param, true);
            }

            $isComplex = false;
            if (is_file($rootpath . $blockId . '/build.json')) {
                $isComplex = true;
            }

            if (!empty($param['title'])) {
                self::$allblocks[$blockId] = [
                    'name'      => $blockId,
                    'groupname' => $groupname,
                    'islocal'   => $islocal,
                    'title'     => $param['title'],
                    'sort'      => (int)($param['sort'] ?? 500),
                    'iscomplex' => $isComplex,
                ];
            }
        }

        return true;
    }

    public static function getBuildJson(string $blockId)
    {
        if (isset(self::$allblocks[$blockId])) {
            $block = self::$allblocks[$blockId];

            if ($block['iscomplex']) {
                $blockPath = self::getGroupPath($block['groupname'], $block['islocal']) . $block['name'] . '/';

                return file_get_contents($blockPath . 'build.json');
            }
        }

        return '';
    }

    protected static function sortByNum($input, $key)
    {
        usort($input, function ($a, $b) use ($key) {
            return ($a[$key] < $b[$key]) ? -1 : 1;
        });

        return $input;
    }

    /**
     * @throws AdminPageException
     */
    public static function createBlock($blockId, string $buildJson)
    {
        if (strpos($blockId, 'complex_') !== 0) {
            $blockId = 'complex_' . $blockId;
        }

        if (is_file(self::getAdminBlockPath($blockId) . 'script.js')) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_complex_err_exists'));
        }

        return self::updateBlock($blockId, $buildJson);
    }

    public static function deleteBlock($blockId)
    {
        if (isset(self::$allblocks[$blockId])) {
            $block = self::$allblocks[$blockId];
            if ($block['iscomplex']) {
                $blockPath = self::getGroupPath($block['groupname'], $block['islocal']) . $block['name'] . '/';
                self::deletePath($blockPath);
                self::deleteComponentTemplate($block['name']);
            }
        }
    }

    /**
     * @throws AdminPageException
     */
    public static function updateBlock($blockId, string $buildJson)
    {
        if (empty($blockId)) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_complex_err_name'));
        }

        $buildJson = json_decode($buildJson, true);
        if (!is_array($buildJson)) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_complex_err_build'));
        }

        if (empty($buildJson['title'])) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_complex_err_title'));
        }

        if (empty($buildJson['sort']) || !is_numeric($buildJson['sort'])) {
            $buildJson['sort'] = 500;
        }

        $adminBlockPath = self::createPath(self::getAdminBlockPath($blockId));

        $layouts = self::convertBuild($buildJson['layouts']);;
        $areas = self::extractAreas($layouts);

        file_put_contents(
            $adminBlockPath . 'build.json',
            json_encode(
                $buildJson,
                JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE
            )
        );

        file_put_contents(
            $adminBlockPath . 'script.js',
            Module::templater(
                '/templates/complex_block/script.js.php',
                [
                    'blockName' => $blockId,
                    'areas'     => $areas,
                ]
            )
        );

        file_put_contents(
            $adminBlockPath . 'template.html',
            Module::templater(
                '/templates/complex_block/template-html.php',
                [
                    'blockName' => $blockId,
                    'layouts'   => $layouts,
                ]
            )
        );

        file_put_contents(
            self::getComponentTemplatePath() . $blockId . '.php',
            Module::templater(
                '/templates/complex_block/template-php.php',
                [
                    'blockName' => $blockId,
                    'layouts'   => $layouts,
                ]
            )
        );

        $configJson = [];
        //update config if exists
        if (is_file($adminBlockPath . 'config.json')) {
            $configJson = file_get_contents($adminBlockPath . 'config.json');
            $configJson = json_decode($configJson, true);
            $configJson = is_array($configJson) ? $configJson : [];
        }

        file_put_contents(
            $adminBlockPath . 'config.json',
            json_encode(
                array_merge(
                    $configJson,
                    [
                        'title' => $buildJson['title'],
                        'sort'  => $buildJson['sort'],
                    ]
                ), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE
            )
        );

        return $blockId;
    }

    protected static function getComponentTemplatePath()
    {
        $local = $_SERVER['DOCUMENT_ROOT'] . '/local/components/sprint.editor/blocks/templates/.default/';
        $path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/sprint.editor/blocks/templates/.default/';

        if (is_dir($local)) {
            return $local;
        }

        return $path;
    }

    protected static function getAdminBlockPath($blockId)
    {
        $local = $_SERVER['DOCUMENT_ROOT'] . '/local/admin/sprint.editor/complex/' . $blockId . '/';
        $path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/complex/' . $blockId . '/';

        if (is_dir($local)) {
            return $local;
        }

        return $path;
    }

    protected static function createPath($path)
    {
        if (!is_dir($path)) {
            mkdir($path, BX_DIR_PERMISSIONS, true);
        }

        return $path;
    }

    protected static function deletePath($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::deletePath("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    protected static function deleteComponentTemplate($blockId)
    {
        $file = self::getComponentTemplatePath() . $blockId . '.php';
        if (is_file($file)) {
            unlink($file);
        }
    }

    protected static function convertBuild($layouts)
    {
        $bcounter = [];
        $aindex = 1;

        foreach ($layouts as $lindex => $layout) {
            foreach ($layout['columns'] as $cindex => $column) {
                foreach ($column['blocks'] as $bindex => $bname) {
                    $column['blocks'][$bindex] = [
                        'blockName' => $bname,
                        'dataKey'   => $bname . ($bcounter[$bname] ?? ''),
                        'container' => '.sp-area-' . $aindex,
                        'areaclass' => 'sp-area-' . $aindex,
                    ];
                    if (isset($bcounter[$bname])) {
                        $bcounter[$bname]++;
                    } else {
                        $bcounter[$bname] = 1;
                    }
                    $aindex++;
                }
                $layout['columns'][$cindex] = $column;
            }
            $layouts[$lindex] = $layout;
        }

        return $layouts;
    }

    protected static function extractAreas($layouts)
    {
        $areas = [];
        foreach ($layouts as $layout) {
            foreach ($layout['columns'] as $column) {
                foreach ($column['blocks'] as $block) {
                    $areas[] = [
                        'blockName' => $block['blockName'],
                        'dataKey'   => $block['dataKey'],
                        'container' => $block['container'],
                    ];
                }
            }
        }
        return $areas;
    }
}
