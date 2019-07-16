<?php


namespace Sprint\Editor;

class AdminEditor
{

    protected static $initCounts = 0;

    protected static $css = [];
    protected static $js = [];

    protected static $parameters = [];
    protected static $templates = [];

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

        $params = array_merge([
            'uniqId' => '',
            'value' => '',
            'inputName' => '',
            'defaultValue' => '',
            'userSettings' => '',
        ], $params);

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

        $userSettings = [];

        if (!empty($params['userSettings']['SETTINGS_NAME'])) {
            self::registerSettingsAssets($params['userSettings']['SETTINGS_NAME']);
            $userSettings = self::loadSettings($params['userSettings']['SETTINGS_NAME']);
        }

        $filteredSelect = self::filterSelect($userSettings);

        if (empty($filteredSelect['layouts'])) {
            $file = '/templates/admin_editor_light.php';
        } else {
            $file = '/templates/admin_editor.php';
        }

        return self::renderFile(Module::getModuleDir() . $file, [
            'jsonValue' => json_encode(Locale::convertToUtf8IfNeed($value)),
            'selectValues' => $filteredSelect,
            'templates' => Locale::convertToWin1251IfNeed(self::$templates),
            'jsonParameters' => json_encode(Locale::convertToUtf8IfNeed(self::$parameters)),
            'jsonUserSettings' => json_encode(Locale::convertToUtf8IfNeed($userSettings)),
            'enableChange' => $enableChange,
            'inputName' => $params['inputName'],
            'uniqId' => $params['uniqId'],
            'firstRun' => (self::$initCounts == 1) ? 1 : 0,
        ]);
    }

    protected static function loadSettings($settingsName)
    {
        $settingsFile = Module::getSettingsDir() . $settingsName . '.php';

        $settings = [];
        if ($settingsName && is_file($settingsFile)) {
            include $settingsFile;
        }

        $settings = array_merge([
            'title' => $settingsName,
            'enable_blocks' => [],
            'layout_classes' => [],
            'block_settings' => [],
        ], $settings);

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

        $directory = new \DirectoryIterator($dir);
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
                'blocks' => $value,
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
                'version' => 2,
                'blocks' => $value['blocks'],
                'layouts' => $newlayots,
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
            \CUtil::InitJSCore(["jquery"]);
        }

        \CUtil::InitJSCore(['translit']);

        if (Module::getDbOption('load_jquery_ui') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        }

        if (Module::getDbOption('load_dotjs') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/doT-master/doT.min.js');
        }

        $APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/trumbowyg/ui/trumbowyg.css');
        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/trumbowyg/trumbowyg.js');

        $APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/trumbowyg/plugins/mycss/ui/trumbowyg.mycss.css');
        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/trumbowyg/plugins/mycss/trumbowyg.mycss.js');

        if (Locale::isWin1251()) {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/trumbowyg/langs/ru.windows-1251.js');
        } else {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/trumbowyg/langs/ru.min.js');
        }

        $APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/sprint_editor.css');
        foreach (self::$css as $val) {
            $APPLICATION->SetAdditionalCSS($val);
        }

        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/sprint_editor.js');
        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/sprint_editor_light.js');
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

        $selectBlocks = [];

        $iterator = new \DirectoryIterator($rootpath);
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

            if (!empty($param['title'])) {
                $selectBlocks[] = $param;
            }

            self::$parameters[$blockName] = $param;
        }

        if (empty($selectBlocks)) {
            return false;
        }

        self::sortByNum($selectBlocks, 'sort');

        self::$selectValues['blocks_' . $groupname] = [
            'title' => GetMessage('SPRINT_EDITOR_group_' . $groupname),
            'type' => 'blocks_' . $groupname,
            'blocks' => Locale::convertToWin1251IfNeed($selectBlocks),
        ];

    }

    protected static function registerLayouts()
    {
        $selectLayouts = [];
        for ($num = 1; $num <= 4; $num++) {
            $selectLayouts[] = [
                'title' => GetMessage('SPRINT_EDITOR_layout_type' . $num),
                'name' => 'layout_' . $num,
            ];
        }

        self::$selectValues['layouts'] = [
            'title' => GetMessage('SPRINT_EDITOR_group_layout'),
            'type' => 'layouts',
            'blocks' => Locale::convertToWin1251IfNeed($selectLayouts),
        ];
    }

    public static function registerPacks()
    {
        $packs = [];

        $dir = Module::getPacksDir();

        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if ($item->getExtension() != 'json') {
                continue;
            }

            $packuid = $item->getBasename('.' . $item->getExtension());

            $content = file_get_contents($item->getPathname());
            $content = json_decode($content, true);

            if ($content && isset($content['blocks']) && $content['layouts']) {

                $packname = !empty($content['packname']) ? $content['packname'] : $packuid;

                $packs[] = [
                    'name' => 'pack_' . $packuid,
                    'title' => $packname,
                ];

            }

        }

        if (empty($packs)) {
            return false;
        }

        self::sortByStr($packs, 'title');

        $result = [
            'title' => GetMessage('SPRINT_EDITOR_group_packs'),
            'type' => 'packs',
            'blocks' => Locale::convertToWin1251IfNeed($packs),
        ];

        self::$selectValues['packs'] = $result;

        return $result;
    }

    protected static function filterSelect($userSettings)
    {
        $localValues = self::$selectValues;

        if (!empty($userSettings['block_disabled'])) {
            $localValues = [];
            foreach (self::$selectValues as $groupType => $group) {
                $localBlocks = [];
                foreach ($group['blocks'] as $blockIndex => $block) {
                    if (!in_array($block['name'], $userSettings['block_disabled'])) {
                        $localBlocks[] = $block;
                    }
                }
                if (!empty($localBlocks)) {
                    $localValues[$groupType] = [
                        'title' => $group['title'],
                        'type' => $group['type'],
                        'blocks' => $localBlocks,

                    ];
                }
            }
        } elseif (!empty($userSettings['block_enabled'])) {
            $localValues = [];
            foreach (self::$selectValues as $groupType => $group) {
                $localBlocks = [];
                foreach ($group['blocks'] as $blockIndex => $block) {
                    if (in_array($block['name'], $userSettings['block_enabled'])) {
                        $localBlocks[] = $block;
                    }
                }
                if (!empty($localBlocks)) {
                    $localValues[$groupType] = [
                        'title' => $group['title'],
                        'type' => $group['type'],
                        'blocks' => $localBlocks,

                    ];
                }
            }
        }

        if (!empty($userSettings['layout_enabled'])) {
            foreach (self::$selectValues as $groupType => $group) {
                if ($groupType == 'layouts') {
                    $localBlocks = [];
                    foreach ($group['blocks'] as $block) {
                        if (in_array($block['name'], $userSettings['layout_enabled'])) {
                            $localBlocks[] = $block;
                        }
                    }
                    if (!empty($localBlocks)) {
                        $localValues[$groupType] = [
                            'title' => $group['title'],
                            'type' => $group['type'],
                            'blocks' => $localBlocks,
                        ];
                    }
                }
            }
        }

        return $localValues;
    }

    public static function renderFile($file, $vars = [])
    {
        if (is_array($vars)) {
            extract($vars, EXTR_SKIP);
        }

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include $file;

        $html = ob_get_clean();
        return $html;
    }

    protected static function sortByNum(&$input = [], $key = 'sort')
    {
        usort($input, function ($a, $b) use ($key) {
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            return ($a[$key] < $b[$key]) ? -1 : 1;
        });
    }

    protected static function sortByStr(&$input = [], $key = 'title')
    {
        usort($input, function ($a, $b) use ($key) {
            return strcmp($a[$key], $b[$key]);
        });
    }
}
