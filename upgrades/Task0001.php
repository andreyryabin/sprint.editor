<?php

namespace Sprint\Editor;

class Task0001 extends Upgrade
{

    public function getDescription() {
        return 'Обновить скрипты';
    }

    public function execute() {

        /** @var $tmpmodule \sprint_editor */
        $tmpmodule = \CModule::CreateModuleObject('sprint.editor');
        $tmpmodule->afterInstallCopyAdmin();

        $this->out('Скрипты обновлены');

    }

}