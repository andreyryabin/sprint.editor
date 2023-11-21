<?php

namespace Sprint\Editor;

use CUtil;
use DirectoryIterator;

class AdminEditor
{
    protected static $initCounts          = 0;
    protected static $css                 = [];
    protected static $js                  = [];
    protected static $allblocks           = [];
    protected static $templates           = [];
    protected static $baseBlockSettings   = [];
    protected static $baseComplexSettings = [];

    public static function init($params)
    {
        self::$initCounts++;

        if (self::$initCounts == 1) {
            self::registerBlocks('blocks', false, false);
            self::registerBlocks('blocks', true, false);

            self::registerBlocks('my', false, true);
            self::registerBlocks('my', true, true);

            self::registerBlocks('complex', false, true);
            self::registerBlocks('complex', true, true);

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

        $userSettingsName = '';
        if (!empty($params['userSettings']['SETTINGS_NAME'])) {
            $userSettingsName = (string)$params['userSettings']['SETTINGS_NAME'];
        }

        if ($userSettingsName) {
            self::registerSettingsAssets($userSettingsName);
            $userSettings = self::loadSettings($userSettingsName);
        } else {
            //default setings (simple editor)
            $userSettings = [
                'layout_enabled'   => [],
                'block_settings'   => [],
                'complex_settings' => [],
            ];
        }

        $editorValue = self::prepareValue($params['value']);
        if (empty($editorValue['blocks']) && empty($editorValue['layouts'])) {
            $editorValue = self::prepareValue($params['defaultValue']);
        }

        if (empty($editorValue['blocks']) && empty($editorValue['layouts'])) {
            $editorValue = [
                'packname' => '',
                'version'  => 2,
                'blocks'   => [],
                'layouts'  => [
                    [
                        'settings' => [],
                        'columns'  => [
                            [
                                'css' => '',
                            ],
                        ],
                    ],
                ],
            ];
        }

        $events = GetModuleEvents("sprint.editor", "OnBeforeShowEditorBlocks", true);
        foreach ($events as $aEvent) {
            ExecuteModuleEventEx($aEvent, [&$editorValue['blocks']]);
        }

        //обязательные настройки блоков
        $userSettings['block_settings'] = array_merge(
            self::$baseBlockSettings,
            $userSettings['block_settings']
        );

        $userSettings['complex_settings'] = array_merge(
            self::$baseComplexSettings,
            $userSettings['complex_settings']
        );

        $saveEmpty = false;
        if ($params['inputName'] == 'pack_content') {
            $saveEmpty = true;
        }

        if (isset($userSettings['wide_mode'])) {
            $wideMode = (bool)$userSettings['wide_mode'];
            unset($userSettings['wide_mode']);
        } else {
            $wideMode = (bool)($params['userSettings']['WIDE_MODE'] ?? false);
        }

        if (isset($userSettings['enable_change'])) {
            $enableChange = (bool)$userSettings['enable_change'];
            unset($userSettings['enable_change']);
        } else {
            $enableChange = !($params['userSettings']['DISABLE_CHANGE'] ?? true);
        }

        $blocksToolbar = self::getBlocksToolbar($userSettings);

        $layoutsToolbar = Locale::convertToWin1251IfNeed(
            array_merge(
                self::filterLayouts($userSettings),
                self::registerPacks($userSettingsName)
            )
        );

        return Module::templater(
            '/templates/admin_editor.php',
            [
                'jsonEditorValue'    => json_encode(Locale::convertToUtf8IfNeed($editorValue)),
                'jsonBlocksToolbar'  => json_encode(Locale::convertToUtf8IfNeed($blocksToolbar)),
                'jsonLayoutsToolbar' => json_encode(Locale::convertToUtf8IfNeed($layoutsToolbar)),
                'jsonBlocksConfigs'  => json_encode(Locale::convertToUtf8IfNeed(self::$allblocks)),
                'jsonUserSettings'   => json_encode(Locale::convertToUtf8IfNeed($userSettings)),
                'jsonTemplates'      => json_encode(Locale::convertToWin1251IfNeed(self::$templates)),
                'userSettingsName'   => $userSettingsName,
                'inputName'          => $params['inputName'],
                'uniqId'             => $params['uniqId'],
                'editorName'         => $params['editorName'],
                'saveEmpty'          => $saveEmpty,
                'wideMode'           => $wideMode,
                'enableChange'       => $enableChange,
                'firstRun'           => (self::$initCounts == 1) ? 1 : 0,
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
                'title'            => $settingsName,
                'layout_classes'   => [],
                'layout_titles'    => [],
                'classes_titles'   => [],
                'block_settings'   => [],
                'complex_settings' => [],
            ], $settings
        );

        //load titles
        foreach (['type1', 'type2', 'type3', 'type4'] as $ltype) {
            if (empty($settings['layout_titles'][$ltype])) {
                $settings['layout_titles'][$ltype] = GetMessage('SPRINT_EDITOR_layout_' . $ltype);
            }
        }

        //convert layout_titles to classes_titles
        foreach ($settings['layout_titles'] as $name => $title) {
            if (!preg_match('/type\d+/', $name)) {
                $settings['classes_titles'][$name] = $title;
                unset($settings['layout_titles'][$name]);
            }
        }

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

            foreach ($value['layouts'] as $layout) {
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
        foreach ($value['blocks'] as $block) {
            if ($block['name'] == 'text' && !empty($block['value'])) {
                $search .= ' ' . $block['value'];
            }
            if ($block['name'] == 'htag' && !empty($block['value'])) {
                $search .= ' ' . $block['value'];
            }
            if ($block['name'] == 'accordion' && !empty($block['items'])) {
                foreach ($block['items'] as $accordionTab) {
                    $search .= ' ' . $accordionTab['title'];
                    foreach ($accordionTab['blocks'] as $accordionTabBlock) {
                        if ($accordionTabBlock['name'] == 'text' && !empty($accordionTabBlock['value'])) {
                            $search .= ' ' . $accordionTabBlock['value'];
                        }
                        if ($accordionTabBlock['name'] == 'htag' && !empty($accordionTabBlock['value'])) {
                            $search .= ' ' . $block['value'];
                        }
                    }
                }
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

        CUtil::InitJSCore(["jquery"]);
        CUtil::InitJSCore(['translit']);

        if (Module::getDbOption('load_jquery_ui') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/jquery-ui-1.13.2.custom/jquery-ui.js');
        }

        if (Module::getDbOption('load_dotjs') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/doT-master/doT.min.js');
        }

        $APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/sprint_editor.css?2');
        foreach (self::$css as $val) {
            $APPLICATION->SetAdditionalCSS($val);
        }

        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/sprint_editor.js?2');
        foreach (self::$js as $val) {
            $APPLICATION->AddHeadScript($val);
        }
    }

    protected static function registerBlocks($groupname, $islocal, $checkname)
    {
        if ($islocal) {
            $webpath = '/local/admin/sprint.editor/' . $groupname . '/';
        } else {
            $webpath = '/bitrix/admin/sprint.editor/' . $groupname . '/';
        }
        $rootpath = Module::getDocRoot() . $webpath;

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

            if (!empty($param['css']) && is_array($param['css'])) {
                foreach ($param['css'] as $css) {
                    self::$css[] = $css;
                }
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

            if (is_file($rootpath . $blockName . '/style.css')) {
                self::$css[] = $webpath . $blockName . '/style.css';
            }

            if (is_file($rootpath . $blockName . '/script.js')) {
                self::$js[] = $webpath . $blockName . '/script.js';
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

            if (!empty($param['settings']) && is_array($param['settings'])) {
                self::$baseBlockSettings[$blockName] = $param['settings'];
            }

            if (!empty($param['complex_settings']) && is_array($param['complex_settings'])) {
                self::$baseComplexSettings[$blockName] = $param['complex_settings'];
            }

            unset($param['templates']);
            unset($param['css']);
            unset($param['js']);
            unset($param['isUtf8']);
            unset($param['isWin1251']);
            unset($param['settings']);
            unset($param['complex_settings']);

            self::$allblocks[$blockName] = $param;
        }

        return true;
    }

    public static function registerPacks($userSettingsName)
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

            if (isset($content['userSettingsName'])) {
                if ($content['userSettingsName'] != $userSettingsName) {
                    continue;
                }
            }

            $title = !empty($content['packname']) ? $content['packname'] : $packuid;

            $packs[$packuid] = [
                'name'  => $packuid,
                'title' => $title,
            ];
        }

        return Locale::convertToWin1251IfNeed(
            self::sortByStr($packs, 'title')
        );
    }

    protected static function getBlocksToolbar($userSettings)
    {
        $filteredBlocks = self::filterBlocks($userSettings);

        $filteredBlocks = self::setCustomTitles($filteredBlocks, $userSettings);

        $filteredBlocks = self::setCustomConfigs($filteredBlocks, $userSettings);

        if (!empty($userSettings['block_toolbar'])) {
            $blocksToolbar = $userSettings['block_toolbar'];
            foreach ($blocksToolbar as $toolbarIndex => $toolbarItem) {
                $filteredItemBlocks = [];
                foreach ($toolbarItem['blocks'] as $blockName) {
                    if (isset($filteredBlocks[$blockName])) {
                        $filteredItemBlocks[] = $filteredBlocks[$blockName];
                    }
                }
                $blocksToolbar[$toolbarIndex]['blocks'] = $filteredItemBlocks;
            }
        } else {
            $blocksToolbar = [];
            foreach (['blocks', 'complex', 'my'] as $groupname) {
                $filteredItemBlocks = array_filter(
                    $filteredBlocks,
                    function ($block) use ($groupname) {
                        return ($block['groupname'] == $groupname);
                    }
                );
                $blocksToolbar[] = [
                    'title'  => GetMessage('SPRINT_EDITOR_group_' . $groupname),
                    'blocks' => Locale::convertToWin1251IfNeed(
                        self::sortBlocksByUserSettings(
                            $userSettings,
                            $filteredItemBlocks
                        )
                    ),
                ];
            }
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
            $blocks = array_filter(
                self::$allblocks,
                function ($block) use ($userSettings) {
                    return in_array($block['name'], $userSettings['block_enabled']);
                }
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

    protected static function setCustomConfigs($blocks, $userSettings)
    {
        if (!empty($userSettings['block_configs'])) {
            foreach ($userSettings['block_configs'] as $name => $config) {
                if (isset($blocks[$name]) && is_array($config)) {
                    $blocks[$name] = array_merge($blocks[$name], $config);
                }
            }
        }
        return $blocks;
    }

    protected static function setCustomTitles($blocks, $userSettings)
    {
        if (!empty($userSettings['block_titles'])) {
            foreach ($userSettings['block_titles'] as $name => $title) {
                if (isset($blocks[$name])) {
                    $blocks[$name]['title'] = $title;
                }
            }
        }
        return $blocks;
    }

    protected static function filterLayouts($userSettings)
    {
        $layouts = [];
        if (!empty($userSettings['layout_enabled'])) {
            $titles = (array)($userSettings['layout_titles'] ?? []);

            foreach ($userSettings['layout_enabled'] as $layoutName) {
                [$prefix, $num] = explode('_', $layoutName);
                if ($prefix == 'layout' && is_numeric($num)) {
                    $layouts[] = [
                        'name'  => 'layout_' . $num,
                        'title' => $titles['type' . $num] ?? $layoutName,
                    ];
                }
            }
        }

        return $layouts;
    }

    protected static function sortByNum($input, $key)
    {
        usort($input, function ($a, $b) use ($key) {
            return ($a[$key] < $b[$key]) ? -1 : 1;
        });

        return $input;
    }

    protected static function sortByStr($input, $key)
    {
        usort($input, function ($a, $b) use ($key) {
            return strcmp($a[$key], $b[$key]);
        });

        return $input;
    }

    protected static function sortBlocksByUserSettings($userSettings, array $filteredBlocks)
    {
        $sortMethod = $userSettings['block_sort'] ?? '';

        if ($sortMethod == 'title') {
            return self::sortByStr($filteredBlocks, 'title');
        } else {
            return self::sortByNum($filteredBlocks, 'sort');
        }
    }
}
