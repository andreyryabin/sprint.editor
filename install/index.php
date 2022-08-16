<?php

class sprint_editor extends CModule
{
    public $MODULE_ID = "sprint.editor";
    public $MODULE_NAME;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        include(__DIR__ . '/../locale/ru.php');

        $this->MODULE_NAME = GetMessage("SPRINT_EDITOR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("SPRINT_EDITOR_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = GetMessage("SPRINT_EDITOR_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("SPRINT_EDITOR_PARTNER_URI");
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'sprint.editor', '\\Sprint\\Editor\\IblockPropertyEditor', 'GetUserTypeDescription');
        RegisterModuleDependences('main', 'OnUserTypeBuildList', 'sprint.editor', '\\Sprint\\Editor\\UserTypeEditor', 'GetUserTypeDescription');

        $this->afterInstallCopyAdmin();
        $this->afterInstallCopyPublic();
    }

    public function afterInstallCopyAdmin()
    {
        CopyDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
    }

    public function afterInstallCopyPublic()
    {
        CopyDirFiles(__DIR__ . "/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
    }

    public function DoUninstall()
    {
        DeleteDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        DeleteDirFiles(__DIR__ . "/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components");

        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'sprint.editor');
        UnRegisterModuleDependences('main', 'OnUserTypeBuildList', 'sprint.editor');
        UnRegisterModule($this->MODULE_ID);
    }
}
