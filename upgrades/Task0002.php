<?php

namespace Sprint\Editor;

class Task0002 extends Upgrade
{

    public function __construct() {
        $this->addButton('execute', GetMessage('SPRINT_EDITOR_BTN_EXECUTE'));
        $this->setDescription('Обновить компоненты в /bitrix/components/sprint.editor/');
    }

    public function execute() {

        /** @var $tmpmodule \sprint_editor */
        $tmpmodule = \CModule::CreateModuleObject('sprint.editor');
        $tmpmodule->afterInstallCopyPublic();

        $this->out('Компоненты обновлены');

    }

}