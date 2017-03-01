<?php


namespace Sprint\Editor;

class UserTypeEditor
{

    function GetUserTypeDescription() {
        return array(
            "USER_TYPE_ID" => "sprint_editor",
            "CLASS_NAME" => "Sprint\\Editor\\UserTypeEditor",
            "DESCRIPTION" => GetMessage("SPRINT_EDITOR_TITLE"),
            "BASE_TYPE" => "string",
        );
    }


    public function GetAdminListViewHTML($arUserField, $arHtmlControl) {
        return 'text';
    }

    public function GetEditFormHTML($arUserField, $arHtmlControl) {
        return AdminEditor::init(
            $arUserField['ID'],
            $arUserField['VALUE'],
            $arHtmlControl['NAME']
        );
    }

    public function OnSearchIndex($arUserField) {

        return 'text';
    }

    function GetDBColumnType($arUserField)
    {
        global $DB;
        switch(strtolower($DB->type))
        {
            case "mysql":
                return "text";
            case "oracle":
                return "varchar2(2000 char)";
            case "mssql":
                return "varchar(2000)";
        }
    }

}