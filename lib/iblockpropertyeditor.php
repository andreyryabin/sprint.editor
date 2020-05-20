<?php

namespace Sprint\Editor;

class IblockPropertyEditor
{
    public function GetUserTypeDescription()
    {
        return [
            "PROPERTY_TYPE"        => "S",
            "USER_TYPE"            => "sprint_editor",
            "DESCRIPTION"          => GetMessage('SPRINT_EDITOR_TITLE'),
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            "GetSearchContent"     => [__CLASS__, "GetSearchContent"],
            "GetSettingsHTML"      => [__CLASS__, "GetSettingsHTML"],
            "PrepareSettings"      => [__CLASS__, "PrepareSettings"],
            "GetPublicEditHTML"    => [__CLASS__, "GetPublicEditHTML"],
        ];
    }

    public function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return 'text';
    }

    public function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $settings = self::PrepareSettings($arProperty);
        $settings = $settings['USER_TYPE_SETTINGS'];

        if (self::isSettingsPage()) {
            $settings['DISABLE_CHANGE'] = '';
            //$settings['SETTINGS_NAME'] = '';
        }

        return AdminEditor::init(
            [
                'uniqId'       => $arProperty['ID'],
                'value'        => $value['VALUE'],
                'inputName'    => $strHTMLControlName['VALUE'],
                'defaultValue' => $arProperty['~DEFAULT_VALUE'],
                'userSettings' => $settings,
            ]
        );
    }

    public function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName);
    }

    public function GetSearchContent($arProperty, $value, $strHTMLControlName)
    {
        return AdminEditor::getSearchIndex($value["VALUE"]);
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = [
            "HIDE" => [
                "FILTRABLE",
                "ROW_COUNT",
                "COL_COUNT",
                "SMART_FILTER",
                "WITH_DESCRIPTION",
                "HINT",
                "MULTIPLE_CNT",
                "MULTIPLE",
                "IS_REQUIRED",
            ],
            "SET"  => [
                "FILTRABLE"        => "N",
                "SMART_FILTER"     => "N",
                "IS_REQUIRED"      => "N",
                "MULTIPLE"         => "N",
                "SECTION_PROPERTY" => "Y",
            ],
        ];

        $settings = self::PrepareSettings($arProperty);
        $settings = $settings['USER_TYPE_SETTINGS'];
        $userfiles = AdminEditor::getUserSettingsFiles();

        return AdminEditor::renderFile(
            Module::getModuleDir() . '/templates/iblock_property.php', [
            'inputName' => $strHTMLControlName['NAME'],
            'settings'  => $settings,
            'userfiles' => $userfiles,
        ]
        );
    }

    public static function PrepareSettings($arProperty)
    {
        $settings = $arProperty['USER_TYPE_SETTINGS'];
        $newsettings = [];

        foreach (['DISABLE_CHANGE', 'SETTINGS_NAME'] as $val) {
            $newsettings[$val] = !empty($settings[$val]) ? $settings[$val] : '';
        }

        return ['USER_TYPE_SETTINGS' => $newsettings];
    }

    public static function isSettingsPage()
    {
        return ($_SERVER["SCRIPT_NAME"] == '/bitrix/admin/iblock_edit_property.php');
    }
}
