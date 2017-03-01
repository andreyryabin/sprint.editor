<?php


namespace Sprint\Editor;

class IblockPropertyEditor
{

    public function GetUserTypeDescription() {
        return array(
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => "sprint_editor",
            "DESCRIPTION" => GetMessage('SPRINT_EDITOR_TITLE'),
            'GetAdminListViewHTML' => array(__CLASS__, 'GetAdminListViewHTML'),
            'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
        );
    }

    public function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName) {
        return 'text';
    }

    public function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {
        return AdminEditor::init(
            $arProperty['ID'],
            $value['VALUE'],
            $strHTMLControlName['VALUE']
        );
    }


}