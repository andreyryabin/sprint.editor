<?php


namespace Sprint\Editor;

class PublicEditor extends Editor
{

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

        $enableChange = 0;
        if (empty($params['userSettings']['DISABLE_CHANGE'])) {
            $enableChange = 1;
        }

        $showSortButtons = 1;
        $showCopyButtons = 1;
        $userSettings = array();

        if (!empty($params['userSettings']['SETTINGS_NAME'])) {
            self::registerSettingsAssets($params['userSettings']['SETTINGS_NAME']);
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

        return self::renderFile(Module::getModuleDir() . '/templates/public_editor.php', array(
            'selectValues' => $localValues,
            'templates' => self::$templates,
            'jsonValue' => json_encode(Locale::convertToUtf8IfNeed($params['value'])),
            'jsonParameters' => json_encode(Locale::convertToUtf8IfNeed(self::$parameters)),
            'jsonUserSettings' => json_encode(Locale::convertToUtf8IfNeed($userSettings)),
            'showSortButtons' => 0,
            'enableChange' => $enableChange,
            'showCopyButtons' => 0,
            'uniqId' => $params['uniqId'],
            'firstRun' => (self::$initCounts == 1) ? 1 : 0,
        ));
    }

}