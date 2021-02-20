<?php

namespace Sprint\Editor;

class UserTypeEditor
{
    function GetUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => "sprint_editor",
            "CLASS_NAME"   => "Sprint\\Editor\\UserTypeEditor",
            "DESCRIPTION"  => GetMessage("SPRINT_EDITOR_TITLE"),
            "BASE_TYPE"    => "string",
        ];
    }

    public function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        return 'text';
    }

    public function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        return AdminEditor::init(
            [
                'uniqId'       => $arUserField['ID'],
                'value'        => $arUserField['VALUE'],
                'inputName'    => $arHtmlControl['NAME'],
                'userSettings' => $arUserField['SETTINGS'],
                'defaultValue' => $arUserField['SETTINGS']['DEFAULT_VALUE'],
            ]
        );
    }

    public function OnSearchIndex($arUserField)
    {
        return AdminEditor::getSearchIndex($arUserField["VALUE"]);
    }

    public function PrepareSettings($arUserField)
    {
        $settings = $arUserField['SETTINGS'];
        $newsettings = [];

        foreach (['DEFAULT_VALUE', 'DISABLE_CHANGE', 'DISABLE_PACKS', 'SETTINGS_NAME'] as $val) {
            $newsettings[$val] = !empty($settings[$val]) ? $settings[$val] : '';
        }

        return $newsettings;
    }

    public function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
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
                    'DISABLE_PACKS'  => '',
                    'SETTINGS_NAME'  => $settings['SETTINGS_NAME'],
                ],
                'defaultValue' => $arUserField['SETTINGS']['DEFAULT_VALUE'],
            ]
        );

        return AdminEditor::renderFile(
            Module::getModuleDir() . '/templates/user_type.php', [
                'inputName'     => $arHtmlControl['NAME'],
                'settings'      => $settings,
                'userfiles'     => $userfiles,
                'defaultEditor' => $defaultEditor,
            ]
        );
    }

    public function GetDBColumnType($arUserField)
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
