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
            $blockGroups = self::getBlockGroups();
            foreach ($blockGroups as $aGroup) {
                self::registerBlocks($aGroup);
            }
            self::registerAssets();
        }

        $params = array_merge(array(
            'uniqId' => '',
            'value' => '',
            'inputName' => '',
            'defaultValue' => '',
            'userSettings' => ''
        ),$params);

        $rawValue = $params['value'];

        $params['value'] = self::prepareValue($params['value']);
        if (empty($params['value'])){
            $params['value'] = self::prepareValue($params['defaultValue']);
        }

        $events = GetModuleEvents("sprint.editor", "OnBeforeShowEditorBlock", true);
        foreach ($events as $aEvent) {
            foreach ($params['value'] as &$block) {
                ExecuteModuleEventEx($aEvent, array(&$block));
            }
            unset($block);
        }

        $enableChange = 0;
        if (empty($params['userSettings']['DISABLE_CHANGE'])){
            $enableChange = 1;
        }

        $showSortButtons = 1;
        if (empty($params['userSettings']['ENABLE_SORT_BUTTONS'])){
            $showSortButtons = 0;
        }

        return self::renderFile(Module::getModuleDir() . '/templates/admin_editor.php', array(
            'rawValue' => $rawValue,
            'jsonValue' => json_encode(Locale::convertToUtf8IfNeed($params['value'])),
            'selectValues' => Locale::convertToWin1251IfNeed(self::$selectValues),
            'jsonTemplates' => json_encode(Locale::convertToUtf8IfNeed(self::$templates)),
            'jsonParameters' => json_encode(Locale::convertToUtf8IfNeed(self::$parameters)),
            'showSortButtons' => $showSortButtons,
            'enableChange' => $enableChange,
            'inputName' => $params['inputName'],
            'uniqId' => $params['uniqId'],
            'firstRun' => (self::$initCounts == 1) ? 1 : 0,
        ));
    }

    public static function prepareValue($value){
        $value = !empty($value) && is_string($value) ? $value : '[]';
        $value = json_decode(Locale::convertToUtf8IfNeed($value), true);
        $value = (json_last_error() == JSON_ERROR_NONE && is_array($value)) ? $value : array();
        return $value;
    }

    public static function getBlockGroups() {
        $groups = array();
        $rootpath = Module::getDocRoot() . '/bitrix/admin/sprint.editor/';

        if (!is_dir($rootpath)){
            return $groups;
        }

        $iterator = new \DirectoryIterator($rootpath);
        foreach ($iterator as $item) {
            if (!$item->isDir() || $item->isDot()) {
                continue;
            }

            $groupName = $item->getFilename();

            if (!is_file($rootpath . $groupName . '/config.json')) {
                continue;
            }

            $config = file_get_contents($rootpath . $groupName . '/config.json');
            $config = json_decode($config, true);

            if (empty($config['title'])) {
                continue;
            }

            $config['name'] = $groupName;
            $config['enable'] = Module::getDbOption('enable_blocks_' . $groupName, 'yes');
            $groups[] = $config;
        }

        self::sortBySort($groups);

        return $groups;
    }

    public static function getSearchIndex($jsonValue){
        $value = AdminEditor::prepareValue($jsonValue);
        $search = '';
        foreach ($value as $key => $val) {
            if ($val['name'] == 'text' && !empty($val['value'])) {
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

        if (Module::getDbOption('load_jquery_ui') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        }

        if (Module::getDbOption('load_dotjs') == 'yes') {
            $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/doT-master/doT.min.js');
        }

        $APPLICATION->SetAdditionalCSS('/bitrix/admin/sprint.editor/assets/trumbowyg/ui/trumbowyg.min.css');
        $APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/trumbowyg/trumbowyg.min.js');
        if (Locale::isWin1251()){
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

    protected static function registerBlocks($group) {
        $webpath = '/bitrix/admin/sprint.editor/' . $group['name'] . '/';
        $rootpath = Module::getDocRoot() . $webpath;

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

            $param = array();
            if (is_file($rootpath . $blockName . '/config.json')) {
                $param = file_get_contents($rootpath . $blockName . '/config.json');
                $param = json_decode($param, true);
            }

            $param['name'] = $blockName;
            $param['title'] = !empty($param['title']) ? $param['title'] : '';
            $param['hint'] = !empty($param['hint']) ? $param['hint'] : '';

            $param['sort'] = !empty($param['sort']) ? intval($param['sort']) : 500;
            $param['sort'] = ($param['sort'] > 0) ? $param['sort'] : 500;

            $param['group'] = $group;

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

            if (!empty($param['title'])){
                $selectBlocks[] = $param;
            }

            self::$parameters[$blockName] = $param;
        }

        if (!empty($selectBlocks) && $group['enable'] == 'yes') {

            self::sortBySort($selectBlocks);

            self::$selectValues[] = array(
                'title' => $group['title'],
                'blocks' => $selectBlocks
            );
        }

        return true;
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

    protected static function sortBySort(&$input = array()) {
        usort($input, function ($a, $b) {
            if ($a['sort'] == $b['sort']) {
                return 0;
            }
            return ($a['sort'] < $b['sort']) ? -1 : 1;
        });
    }

}