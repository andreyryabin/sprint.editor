<?php

namespace Sprint\Editor;

use CUtil;
use DirectoryIterator;

class AdminEditor
{
    protected static $initCounts   = 0;
    protected static $css          = [];
    protected static $js           = [];
    protected static $allblocks    = [];
    protected static $alllayouts   = [];
    protected static $templates    = [];
    protected static $selectValues = [];

    public static function init($params)
    {
        self::$initCounts++;

        if (self::$initCounts == 1) {
            self::registerPacks();

            self::registerBlocks('blocks', false, false);

            self::registerBlocks('my', false, true);
            self::registerBlocks('my', true, true);

            self::registerLayouts();
            self::registerAssets();
        }

        $params = array_merge(
            [
                'uniqId'       => '',
                'value'        => '',
                'inputName'    => '',
                'defaultValue' => '',
                'userSettings' => '',
            ], $params
        );

        $value = self::prepareValue($params['value']);
        if (empty($value['blocks']) && empty($value['layouts'])) {
            $value = self::prepareValue($params['defaultValue']);
        }

        $events = GetModuleEvents("sprint.editor", "OnBeforeShowEditorBlocks", true);
        foreach ($events as $aEvent) {
            ExecuteModuleEventEx($aEvent, [&$value['blocks']]);
        }

        $enableChange = 0;
        if (empty($params['userSettings']['DISABLE_CHANGE'])) {
            $enableChange = 1;
        }

        //default setings (simple editor)
        $userSettings = [
            'layout_enabled' => [
                'layout_none',
            ],

            'block_settings' => [
                'lists' => [
                    'type' => [
                        'type'    => 'select',
                        'default' => 'ul',
                        'value'   => [
                            'ul' => 'Маркированный',
                            'ol' => 'Нумерованный',
                        ],
                    ],
                ],
            ],
        ];

        if (!empty($params['userSettings']['SETTINGS_NAME'])) {
            self::registerSettingsAssets($params['userSettings']['SETTINGS_NAME']);
            $userSettings = self::loadSettings($params['userSettings']['SETTINGS_NAME']);
        }

        $filteredBlocks = self::filterBlocks($userSettings);
        $blocksToolbar = self::getBlocksToolbar($userSettings, $filteredBlocks);

        $filteredLayouts = self::filterLayouts($userSettings);
        $layoutsToolbar = self::getLayoutsToolbar($userSettings, $filteredLayouts);

        if (empty($filteredLayouts)) {
            $file = '/templates/admin_editor_simple.php';
        } else {
            $file = '/templates/admin_editor.php';
        }

        return self::renderFile(
            Module::getModuleDir() . $file, [
                'jsonValue'        => json_encode(Locale::convertToUtf8IfNeed($value)),
                'blocksToolbar'    => $blocksToolbar,
                'layoutsToolbar'   => $layoutsToolbar,
                'templates'        => Locale::convertToWin1251IfNeed(self::$templates),
                'jsonParameters'   => json_encode(Locale::convertToUtf8IfNeed(self::$allblocks)),
                'jsonUserSettings' => json_encode(Locale::convertToUtf8IfNeed($userSettings)),
                'enableChange'     => $enableChange,
                'inputName'        => $params['inputName'],
                'uniqId'           => $params['uniqId'],
                'firstRun'         => (self::$initCounts == 1) ? 1 : 0,
            ]
        );
    }

    protected static function loadSettings($settingsName)
    {
        $settingsFile = Module::getSettingsDir() . $settingsName . '.php';

        $settings = [];
        if ($settingsName && is_file($settingsFile)) {
            include $settingsFile;
        }

        $settings = array_merge(
            [
                'title'          => $settingsName,
                'layout_classes' => [],
                'block_settings' => [],
            ], $settings
        );

        return $settings;
    }

    public static function registerSettingsAssets($settingsName)
    {
        global $APPLICATION;
        $cssFile = Module::getSettingsDir() . $settingsName . '.css';
        if (is_file($cssFile)) {
            $cssFile = str_replace(Module::getDocRoot(), '', $cssFile);
            $APPLICATION->SetAdditionalCSS($cssFile);
        }
    }

    public static function getUserSettingsFiles()
    {
        $result = ['' => GetMessage('SPRINT_EDITOR_SETTINGS_NAME_NO')];

        $dir = Module::getSettingsDir();

        $directory = new DirectoryIterator($dir);
        foreach ($directory as $item) {
            if ($item->isFile() && $item->getExtension() == 'php') {
                $settingsName = $item->getBasename('.php');
                $settings = self::loadSettings($settingsName);
                $result[$settingsName] = Locale::convertToWin1251IfNeed($settings['title']);
            }
        }
        return $result;
    }

    public static function prepareValue($value)
    {
        $value = str_replace("\xe2\x80\xa8", '\\u2028', $value);
        $value = str_replace("\xe2\x80\xa9", '\\u2029', $value);

        $value = json_decode(Locale::convertToUtf8IfNeed($value), true);
        $value = (json_last_error() == JSON_ERROR_NONE && is_array($value)) ? $value : [];
        return self::prepareValueArray($value);
    }

    public static function prepareValueArray($value)
    {
        /* convert to version 1 */
        if (!empty($value) && !isset($value['layouts'])) {
            foreach ($value as $index => $block) {
                $block['layout'] = '0,0';
                $value[$index] = $block;
            }

            $value = [
                'blocks'  => $value,
                'layouts' => [
                    [''],
                ],
            ];
        }

        /* convert to version 2 */
        if (!empty($value) && !isset($value['version'])) {
            $newlayots = [];

            foreach ($value['layouts'] as $index => $layout) {
                $newcolumns = [];
                foreach ($layout as $column) {
                    $newcolumns[] = [
                        'css' => $column,
                    ];
                }

                $newlayots[] = [
                    'columns' => $newcolumns,
                ];
            }

            $value = [
                'packname' => '',
                'version'  => 2,
                'blocks'   => $value['blocks'],
                'layouts'  => $newlayots,
            ];
        }

        if (!isset($value['blocks'])) {
            $value['blocks'] = [];
        }

        if (!isset($value['layouts'])) {
            $value['layouts'] = [];
        }

        return $value;
    }

    /**
     * Возвращает представление значения свойства для модуля поиска.
     *
     * @param string $jsonValue JSON-строка со значением свойства "Редактор блоков (sprint.editor)".
     *
     * @return string
     */
    public static function getSearchIndex($jsonValue)
    {
        $value = self::prepareValue($jsonValue);
        $search = '';
        foreach ($value['blocks'] as $key => $val) {
            if ($val['name'] == 'text' && !empty($val['value'])) {
                $search .= ' ' . $val['value'];
            }
            if ($val['name'] == 'htag' && !empty($val['value'])) {
                $search .= ' ' . $val['value'];
            }
        }

        foreach (GetModuleEvents('sprint.editor', 'OnGetSearchIndex', true) as $event) {
            $modifiedSearch = ExecuteModuleEventEx($event, [$value, $search]);
            if (is_string($modifiedSearch)) {
                $search = $modifiedSearch;
            }
        }

        return $search;
    }

    protected static function registerAssets()
    {
        global $APPLICATION;

        if (Module::getDbOption('load_jquery') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/jquery-1.11.1.min.js');
        } else {
            CUtil::InitJSCore(["jquery"]);
        }

        CUtil::InitJSCore(['translit']);

        if (Module::getDbOption('load_jquery_ui') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        }

        if (Module::getDbOption('load_dotjs') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/doT-master/doT.min.js');
        }

        $APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/sprint_editor.css');
        foreach (self::$css as $val) {
            $APPLICATION->SetAdditionalCSS($val);
        }

        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/sprint_editor.js');
        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/sprint_editor_simple.js');
        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/sprint_editor_full.js');

        foreach (self::$js as $val) {
            $APPLICATION->AddHeadScript($val);
        }
    }

    protected static function registerBlocks($groupname, $islocal = false, $checkname = true)
    {
        if ($islocal) {
            $webpath = '/local/admin/sprint.editor/' . $groupname . '/';
            $rootpath = Module::getDocRoot() . $webpath;
        } else {
            $webpath = '/bitrix/admin/sprint.editor/' . $groupname . '/';
            $rootpath = Module::getDocRoot() . $webpath;
        }

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

            $param['name'] = $blockName;
            $param['groupname'] = $groupname;
            $param['islocal'] = $islocal;

            $param['title'] = !empty($param['title']) ? $param['title'] : '';
            $param['hint'] = !empty($param['hint']) ? $param['hint'] : '';

            $param['sort'] = !empty($param['sort']) ? intval($param['sort']) : 500;
            $param['sort'] = ($param['sort'] > 0) ? $param['sort'] : 500;

            if (is_file($rootpath . $blockName . '/style.css')) {
                self::$css[] = $webpath . $blockName . '/style.css';
            }

            if (!empty($param['css']) && is_array($param['css'])) {
                foreach ($param['css'] as $css) {
                    self::$css[] = $css;
                }
            }

            if (is_file($rootpath . $blockName . '/script.js')) {
                self::$js[] = $webpath . $blockName . '/script.js';
            }
            if (!empty($param['js']) && is_array($param['js'])) {
                foreach ($param['js'] as $js) {
                    self::$js[] = $js;
                }
            }

            $encodeKey = Locale::isWin1251() ? 'isWin1251' : 'isUtf8';
            if (!empty($param[$encodeKey]['js']) && is_array($param[$encodeKey]['js'])) {
                foreach ($param[$encodeKey]['js'] as $js) {
                    self::$js[] = $js;
                }
            }
            if (!empty($param[$encodeKey]['css']) && is_array($param[$encodeKey]['css'])) {
                foreach ($param[$encodeKey]['css'] as $css) {
                    self::$css[] = $css;
                }
            }

            if (is_file($rootpath . $blockName . '/template.html')) {
                self::$templates[$blockName] = file_get_contents($rootpath . $blockName . '/template.html');
            }

            if (!empty($param['templates']) && is_array($param['templates'])) {
                foreach ($param['templates'] as $val) {
                    $tmp = $blockName . '-' . pathinfo($val, PATHINFO_FILENAME);
                    self::$templates[$tmp] = file_get_contents($rootpath . $blockName . '/' . $val);
                }
            }

            unset($param['templates']);
            unset($param['css']);
            unset($param['js']);

            self::$allblocks[$blockName] = $param;
        }

        return true;
    }

    protected static function registerLayouts()
    {
        for ($num = 1; $num <= 4; $num++) {
            $layoutName = 'layout_' . $num;

            self::$alllayouts[$layoutName] = [
                'title' => GetMessage('SPRINT_EDITOR_layout_type' . $num),
                'name'  => $layoutName,
            ];
        }
    }

    public static function registerPacks()
    {
        $dir = Module::getPacksDir();
        $packs = [];
        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if ($item->getExtension() != 'json') {
                continue;
            }

            $packuid = $item->getBasename('.' . $item->getExtension());

            $content = file_get_contents($item->getPathname());
            $content = json_decode($content, true);
            if (empty($content)) {
                continue;
            }

            if (!isset($content['blocks']) || !isset($content['layouts'])) {
                continue;
            }

            $title = !empty($content['packname']) ? $content['packname'] : $packuid;
            $packName = 'pack_' . $packuid;

            $packs[$packName] = [
                'name'  => $packName,
                'title' => $title,
            ];
        }

        $packs = self::sortByStr($packs, 'title');

        return [
            'title'  => GetMessage('SPRINT_EDITOR_group_packs'),
            'blocks' => Locale::convertToWin1251IfNeed($packs),
        ];
    }

    protected static function filterBlocks($userSettings)
    {
        if (!empty($userSettings['block_disabled'])) {
            $blocks = array_filter(
                self::$allblocks,
                function ($block) use ($userSettings) {
                    return !in_array($block['name'], $userSettings['block_disabled']);
                }
            );
        } elseif (!empty($userSettings['block_enabled'])) {
            $blocks = array_map(
                function ($name) {
                    return self::$allblocks[$name];
                }, $userSettings['block_enabled']
            );
        } else {
            $blocks = self::$allblocks;
        }

        return array_filter(
            $blocks,
            function ($block) {
                return !empty($block['title']);
            }
        );
    }

    protected static function filterLayouts($userSettings)
    {
        if (!empty($userSettings['layout_enabled'])) {
            $layouts = array_map(
                function ($name) {
                    return self::$alllayouts[$name];
                }, $userSettings['layout_enabled']
            );
        } elseif (!empty($userSettings['block_disabled'])) {
            $layouts = array_filter(
                self::$alllayouts,
                function ($layout) use ($userSettings) {
                    return !in_array($layout['name'], $userSettings['block_disabled']);
                }
            );
        } else {
            $layouts = self::$alllayouts;
        }

        return array_filter(
            $layouts,
            function ($layout) {
                return !empty($layout['title']);
            }
        );
    }

    protected static function getBlocksToolbar($userSettings, $filteredBlocks)
    {
        if (!empty($userSettings['block_toolbar'])) {
            $blocksToolbar = $userSettings['block_toolbar'];
            foreach ($blocksToolbar as $toolbarIndex => $toolbarItem) {
                $filteredItemBlocks = [];
                foreach ($toolbarItem['blocks'] as $blockName) {
                    if (isset($filteredBlocks[$blockName])) {
                        $filteredItemBlocks[] = $filteredBlocks[$blockName];
                    }
                }
                $blocksToolbar[$toolbarIndex]['blocks'] = self::sortByNum($filteredItemBlocks, 'sort');
            }
        } else {
            $blocksToolbar = [];
            foreach (['blocks', 'my'] as $groupname) {
                $filteredItemBlocks = array_filter(
                    $filteredBlocks,
                    function ($block) use ($groupname) {
                        return ($block['groupname'] == $groupname);
                    }
                );
                $blocksToolbar[] = [
                    'title'  => GetMessage('SPRINT_EDITOR_group_' . $groupname),
                    'blocks' => Locale::convertToWin1251IfNeed(self::sortByNum($filteredItemBlocks, 'sort')),
                ];
            }
        }

        $blocksToolbar = array_filter(
            $blocksToolbar,
            function ($toolbarItem) {
                return !empty($toolbarItem['blocks']);
            }
        );

        return $blocksToolbar;
    }

    protected static function getLayoutsToolbar($userSettings, $filteredLayouts)
    {
        if (!empty($userSettings['layout_toolbar'])) {
            $layoutsToolbar = [];
        } else {
            $layoutsToolbar = [
                [
                    'title'  => GetMessage('SPRINT_EDITOR_group_layout'),
                    'blocks' => Locale::convertToWin1251IfNeed($filteredLayouts),
                ],
            ];
        }
        return $layoutsToolbar;
    }

    public static function renderFile($file, $vars = [])
    {
        if (is_array($vars)) {
            extract($vars, EXTR_SKIP);
        }

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include $file;

        return ob_get_clean();
    }

    protected static function sortByNum($input = [], $key = 'sort')
    {
        usort(
            $input,
            function ($a, $b) use ($key) {
                if ($a[$key] == $b[$key]) {
                    return 0;
                }
                return ($a[$key] < $b[$key]) ? -1 : 1;
            }
        );

        return $input;
    }

    protected static function sortByStr($input = [], $key = 'title')
    {
        usort(
            $input,
            function ($a, $b) use ($key) {
                return strcmp($a[$key], $b[$key]);
            }
        );

        return $input;
    }
}
