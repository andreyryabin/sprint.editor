<?php


namespace Sprint\Editor;

class AdminEditor
{

    protected static $initCounts = 0;

    protected static $css = array();
    protected static $js = array();

    protected static $parameters = array();
    protected static $templates = array();

    protected static $selectValues = array();

    public static function init($params) {
        self::$initCounts++;

        if (self::$initCounts == 1) {

            self::registerPacks();

            self::registerBlocks('blocks', false, false);
            self::registerBlocks('my', false, true);
            self::registerBlocks('my', true, true);

            self::registerLayouts();


            self::registerAssets();

        }

        $params = array_merge(array(
            'uniqId' => '',
            'value' => '',
            'inputName' => '',
            'defaultValue' => '',
            'userSettings' => ''
        ), $params);

        $value = self::prepareValue($params['value']);
        if (empty($value)) {
            $value = self::prepareValue($params['defaultValue']);
        }

        $events = GetModuleEvents("sprint.editor", "OnBeforeShowEditorBlocks", true);
        foreach ($events as $aEvent) {
            ExecuteModuleEventEx($aEvent, array(&$value['blocks']));
        }

        $enableChange = 0;
        if (empty($params['userSettings']['DISABLE_CHANGE'])) {
            $enableChange = 1;
        }

        $showSortButtons = 1;
        $showCopyButtons = 1;

        $userSettings = array();
        if (!empty($params['userSettings']['SETTINGS_NAME'])) {
            $userSettings = self::loadSettings($params['userSettings']['SETTINGS_NAME']);
        }

        if (!empty($userSettings['block_enabled'])) {
            $localValues = [];
            foreach (self::$selectValues as $groupIndex => $group) {
                $localBlocks = [];
                foreach ($group['blocks'] as $blockIndex => $block) {
                    if (in_array($block['name'], $userSettings['block_enabled'])) {
                        $localBlocks[] = $block;
                    }
                }
                if (!empty($localBlocks)) {
                    $localValues[] = array(
                        'title' => $group['title'],
                        'type' => $group['type'],
                        'blocks' => $localBlocks

                    );
                }
            }
        } else {
            $localValues = self::$selectValues;
        }

        return self::renderFile(Module::getModuleDir() . '/templates/admin_editor.php', array(
            'jsonValue' => json_encode(Locale::convertToUtf8IfNeed($value)),
            'selectValues' => $localValues,
            'jsonTemplates' => json_encode(Locale::convertToUtf8IfNeed(self::$templates)),
            'jsonParameters' => json_encode(Locale::convertToUtf8IfNeed(self::$parameters)),
            'jsonUserSettings' => json_encode(Locale::convertToUtf8IfNeed($userSettings)),
            'showSortButtons' => $showSortButtons,
            'enableChange' => $enableChange,
            'showCopyButtons' => $showCopyButtons,
            'inputName' => $params['inputName'],
            'uniqId' => $params['uniqId'],
            'firstRun' => (self::$initCounts == 1) ? 1 : 0,
        ));
    }

    protected static function loadSettings($settingsName) {
        $settingsFile = Module::getSettingsDir() . $settingsName . '.php';

        $settings = array();
        if ($settingsName && is_file($settingsFile)) {
            include $settingsFile;
        }

        $settings = array_merge(array(
            'title' => $settingsName,
            'enable_blocks' => array(),
            'layout_classes' => array(),
            'block_settings' => array(),
        ), $settings);

        return $settings;
    }

    public static function getUserSettingsFiles() {
        $directory = new \DirectoryIterator(Module::getSettingsDir());

        $result = array('' => GetMessage('SPRINT_EDITOR_SETTINGS_NAME_NO'));
        foreach ($directory as $item) {
            if ($item->isFile() && $item->getExtension() == 'php') {
                $settingsName = $item->getBasename('.php');
                $settings = self::loadSettings($settingsName);
                $result[$settingsName] = $settings['title'];
            }
        }
        return Locale::convertToWin1251IfNeed($result);
    }

    public static function prepareValue($value) {
        $value = str_replace("\xe2\x80\xa8", '\\u2028', $value);
        $value = str_replace("\xe2\x80\xa9", '\\u2029', $value);

        $value = json_decode(Locale::convertToUtf8IfNeed($value), true);
        $value = (json_last_error() == JSON_ERROR_NONE && is_array($value)) ? $value : array();
        return self::prepareValueArray($value);
    }

    public static function prepareValueArray($value) {

        /* convert to version 1 */
        if (!empty($value) && !isset($value['layouts'])) {
            foreach ($value as $index => $block) {
                $block['layout'] = '0,0';
                $value[$index] = $block;
            }

            $value = array(
                'blocks' => $value,
                'layouts' => array(
                    array(''),
                )
            );
        }

        /* convert to version 2 */
        if (!empty($value) && !isset($value['version'])) {

            $newlayots = array();

            foreach ($value['layouts'] as $index => $layout) {

                $newcolumns = array();
                foreach ($layout as $column) {
                    $newcolumns[] = array(
                        'css' => $column
                    );
                }

                $newlayots[] = array(
                    'columns' => $newcolumns
                );

            }


            $value = array(
                'packname' => '',
                'version' => 2,
                'blocks' => $value['blocks'],
                'layouts' => $newlayots
            );

        }


        if (!isset($value['blocks'])) {
            $value['blocks'] = array();
        }

        if (!isset($value['layouts'])) {
            $value['layouts'] = array();
        }

        return $value;
    }

    public static function getSearchIndex($jsonValue) {
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
        return $search;
    }

    protected static function registerAssets() {
        global $APPLICATION;

        if (Module::getDbOption('load_jquery') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/jquery-1.11.1.min.js');
        } else {
            \CUtil::InitJSCore(Array("jquery"));
        }

        \CUtil::InitJSCore(array('translit'));

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

        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/trumbowyg/plugins/cleanpaste/trumbowyg.cleanpaste.js');

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
        foreach (self::$js as $val) {
            $APPLICATION->AddHeadScript($val);
        }
    }

    protected static function registerBlocks($groupname, $islocal = false, $checkname = true) {
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

        $selectBlocks = array();

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

            $param = array();
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

        self::$selectValues[] = array(
            'title' => GetMessage('SPRINT_EDITOR_group_' . $groupname),
            'type' => 'blocks_'. $groupname,
            'blocks' => Locale::convertToWin1251IfNeed($selectBlocks)
        );

    }

    protected static function registerLayouts() {
        $selectLayouts = array();
        for ($num = 1; $num <= 4; $num++) {
            $selectLayouts[] = array(
                'title' => GetMessage('SPRINT_EDITOR_layout_type' . $num),
                'name' => 'layout_' . $num,
            );
        }

        self::$selectValues[] = array(
            'title' => GetMessage('SPRINT_EDITOR_group_layout'),
            'type' => 'layouts',
            'blocks' => Locale::convertToWin1251IfNeed($selectLayouts)
        );
    }

    public static function registerPacks() {
        $packs = array();

        $dir = Module::getPacksDir();

        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if ($item->getExtension() != 'json') {
                continue;
            }

            $packuid = $item->getBasename('.'.$item->getExtension());

            $content = file_get_contents($item->getPathname());
            $content = json_decode($content, true);

            if ($content && isset($content['blocks']) && $content['layouts']) {

                $packname = !empty($content['packname']) ? $content['packname'] : $packuid;

                $packs[] = array(
                    'name' => 'pack_' . $packuid,
                    'title' => $packname,
                );

            }

        }

        self::sortByStr($packs, 'title');

        $result = array(
            'title' => GetMessage('SPRINT_EDITOR_group_packs'),
            'type' => 'packs',
            'blocks' => Locale::convertToWin1251IfNeed($packs)
        );

        self::$selectValues[] = $result;

        return $result;
    }

    public static function renderFile($file, $vars = array()) {
        if (is_array($vars)) {
            extract($vars, EXTR_SKIP);
        }

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include $file;

        $html = ob_get_clean();
        return $html;
    }

    protected static function sortByNum(&$input = array(), $key = 'sort') {
        usort($input, function ($a, $b) use ($key){
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            return ($a[$key] < $b[$key]) ? -1 : 1;
        });
    }

    protected static function sortByStr(&$input = array(), $key = 'title') {
        usort($input, function ($a, $b) use ($key){
            return strcmp($a[$key], $b[$key]);
        });
    }
}