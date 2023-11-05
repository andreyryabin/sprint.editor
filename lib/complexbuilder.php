<?php

namespace Sprint\Editor;

use CUtil;
use DirectoryIterator;
use Sprint\Editor\Exceptions\ComplexBuilderException;

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
        return array_filter(
            self::$allblocks,
            function ($block) {
                return ($block['groupname'] == 'complex' && $block['iscomplex']);
            }
        );
    }

    public static function getBlocksToolbar()
    {
        $blocksToolbar = [];
        foreach (['blocks', 'my'] as $groupname) {
            $filteredItemBlocks = array_filter(
                self::$allblocks,
                function ($block) use ($groupname) {
                    return ($block['groupname'] == $groupname);
                }
            );
            $blocksToolbar[] = [
                'title'  => GetMessage('SPRINT_EDITOR_group_' . $groupname),
                'blocks' => self::sortByNum($filteredItemBlocks, 'sort'),
            ];
        }

        return $blocksToolbar;
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

            $blockName = $item->getFilename();

            if ($checkname) {
                if (strpos($blockName, $groupname) !== 0) {
                    continue;
                }
            }

            $param = [];
            if (is_file($rootpath . $blockName . '/config.json')) {
                $param = file_get_contents($rootpath . $blockName . '/config.json');
                $param = json_decode($param, true);
            }

            $isComplex = false;
            if (is_file($rootpath . $blockName . '/build.json')) {
                $isComplex = true;
            }

            if (!empty($param['title'])) {
                self::$allblocks[$blockName] = [
                    'name'      => $blockName,
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

    public static function getBuildJson(string $blockName)
    {
        if (isset(self::$allblocks[$blockName])) {
            $block = self::$allblocks[$blockName];

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
     * @throws ComplexBuilderException
     */
    public static function createBlock($blockName, string $buildJson)
    {
        if (strpos($blockName, 'complex_') !== 0) {
            $blockName = 'complex_' . $blockName;
        }

        if (is_file(self::getAdminBlockPath($blockName) . 'script.js')) {
            throw new ComplexBuilderException('Такой блок уже есть');
        }

        return self::saveBlock($blockName, $buildJson);
    }

    /**
     * @throws ComplexBuilderException
     */
    public static function updateBlock($blockName, string $buildJson)
    {
        return self::saveBlock($blockName, $buildJson);
    }

    public static function deleteBlock($blockName)
    {
        if (isset(self::$allblocks[$blockName])) {
            $block = self::$allblocks[$blockName];
            if ($block['iscomplex']) {
                $blockPath = self::getGroupPath($block['groupname'], $block['islocal']) . $block['name'] . '/';
                self::deletePath($blockPath);
                self::deleteComponentTemplate($block['name']);
            }
        }
    }

    /**
     * @throws ComplexBuilderException
     */
    protected static function saveBlock($blockName, string $buildJson)
    {
        if (empty($blockName)) {
            throw new ComplexBuilderException('Укажите название блока');
        }

        $buildJson = json_decode($buildJson, true);
        if (!is_array($buildJson)) {
            throw new ComplexBuilderException('Задайте содержимое блока');
        }

        if (empty($buildJson['title'])) {
            throw new ComplexBuilderException('Укажите заголовок блока');
        }

        if (empty($buildJson['sort']) || !is_numeric($buildJson['sort'])) {
            $buildJson['sort'] = 500;
        }

        $adminBlockPath = self::createPath(self::getAdminBlockPath($blockName));

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
                    'blockName' => $blockName,
                    'areas'     => $areas,
                ]
            )
        );

        file_put_contents(
            $adminBlockPath . 'template.html',
            Module::templater(
                '/templates/complex_block/template-html.php',
                [
                    'blockName' => $blockName,
                    'layouts'   => $layouts,
                ]
            )
        );

        file_put_contents(
            $adminBlockPath . 'config.json',
            json_encode([
                'title' => $buildJson['title'],
                'sort'  => $buildJson['sort'],
            ], JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE)
        );

        $componentTemplatePath = self::getComponentTemplatePath();

        file_put_contents(
            $componentTemplatePath . $blockName . '.php',
            Module::templater(
                '/templates/complex_block/template-php.php',
                [
                    'blockName' => $blockName,
                    'layouts'   => $layouts,
                ]
            )
        );

        return $blockName;
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

    protected static function getAdminBlockPath($blockName)
    {
        $local = $_SERVER['DOCUMENT_ROOT'] . '/local/admin/sprint.editor/complex/' . $blockName . '/';
        $path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/complex/' . $blockName . '/';

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

    protected static function deleteComponentTemplate($blockName)
    {
        $componentTemplatePath = self::getComponentTemplatePath();

        $file = $componentTemplatePath . $blockName . '.php';
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
