<?php

Class sprint_editor extends CModule
{
    var $MODULE_ID = "sprint.editor";

    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    var $MODULE_GROUP_RIGHTS = "Y";

    function sprint_editor() {
        $arModuleVersion = array();

        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        include(__DIR__ .'/../loader.php');
        include(__DIR__ .'/../locale/ru.php');

        $this->MODULE_NAME = GetMessage("SPRINT_EDITOR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("SPRINT_EDITOR_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = GetMessage("SPRINT_EDITOR_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("SPRINT_EDITOR_PARTNER_URI");
    }

    function DoInstall() {
        RegisterModule($this->MODULE_ID);
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'sprint.editor', '\\Sprint\\Editor\\IblockPropertyEditor', 'GetUserTypeDescription');
        RegisterModuleDependences('main', 'OnUserTypeBuildList', 'sprint.editor', '\\Sprint\\Editor\\UserTypeEditor', 'GetUserTypeDescription');

        $this->afterInstall();
    }

    function afterInstall(){
        CopyDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
        CopyDirFiles(__DIR__ . "/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
    }

    function DoUninstall() {
        global $DB;

        DeleteDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        DeleteDirFiles(__DIR__ . "/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components");

        $DB->Query('DELETE FROM b_module_to_module WHERE TO_MODULE_ID="sprint.editor"');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'sprint.editor');
        UnRegisterModuleDependences('main', 'OnUserTypeBuildList', 'sprint.editor');
        UnRegisterModule($this->MODULE_ID);
    }

}
