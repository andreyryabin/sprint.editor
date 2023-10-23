<?php

namespace Sprint\Editor;

use CModule;
use sprint_editor;

class Task0001 extends Upgrade
{

    public function __construct() {
        $this->addButton('execute', GetMessage('SPRINT_EDITOR_BTN_EXECUTE'));
        $this->setDescription('Обновить скрипты в /bitrix/admin/sprint.editor/');
    }

    public function execute() {

        /** @var $tmpmodule sprint_editor */
        $tmpmodule = CModule::CreateModuleObject('sprint.editor');
        $tmpmodule->afterInstallCopyAdmin();

        $this->out('Скрипты обновлены');

    }

}
