<?php

namespace Sprint\Editor;

class UserTypeEditor
{
    public static function GetUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => "sprint_editor",
            "CLASS_NAME"   => "Sprint\\Editor\\UserTypeEditor",
            "DESCRIPTION"  => GetMessage("SPRINT_EDITOR_TITLE"),
            "BASE_TYPE"    => "string",
        ];
    }

    public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        return 'text';
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        return AdminEditor::init(
            [
                'uniqId'       => $arUserField['ID'],
                'editorName'   => $arUserField['FIELD_NAME'],
                'value'        => $arUserField['VALUE'],
                'inputName'    => $arHtmlControl['NAME'],
                'userSettings' => $arUserField['SETTINGS'],
                'defaultValue' => $arUserField['SETTINGS']['DEFAULT_VALUE'],
            ]
        );
    }

    public static function OnSearchIndex($arUserField)
    {
        return AdminEditor::getSearchIndex($arUserField["VALUE"]);
    }

    public static function PrepareSettings($arUserField)
    {
        $settings = $arUserField['SETTINGS'];
        $newsettings = [];

        foreach (['DEFAULT_VALUE', 'DISABLE_CHANGE', 'WIDE_MODE', 'SETTINGS_NAME'] as $val) {
            $newsettings[$val] = !empty($settings[$val]) ? $settings[$val] : '';
        }

        return $newsettings;
    }

    public static function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
    {
        $settings = self::PrepareSettings($arUserField);
        $userfiles = AdminEditor::getUserSettingsFiles();

        $defaultEditor = AdminEditor::init(
            [
                'uniqId'       => $arUserField['ID'],
                'value'        => $arUserField['VALUE'],
                'inputName'    => $arHtmlControl['NAME'] . '[DEFAULT_VALUE]',
                'userSettings' => [
                    'DISABLE_CHANGE' => '',
                    'WIDE_MODE'      => '',
                    'SETTINGS_NAME'  => $settings['SETTINGS_NAME'],
                ],
                'defaultValue' => $arUserField['SETTINGS']['DEFAULT_VALUE'],
            ]
        );

        return Module::templater(
            '/templates/user_type.php',
            [
                'inputName'     => $arHtmlControl['NAME'],
                'settings'      => $settings,
                'userfiles'     => $userfiles,
                'defaultEditor' => $defaultEditor,
            ]
        );
    }

    public static function GetDBColumnType($arUserField)
    {
        global $DB;
        switch (strtolower($DB->type)) {
            case "mysql":
                return "longtext";
            case "mssql":
                return "varchar(max)";
        }
    }
}
