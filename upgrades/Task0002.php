<?php

namespace Sprint\Editor;

class Task0002 extends Upgrade
{

    public function getDescription() {
        return 'Обновить компоненты';
    }

    public function execute() {

        /** @var $tmpmodule \sprint_editor */
        $tmpmodule = \CModule::CreateModuleObject('sprint.editor');
        $tmpmodule->afterInstallCopyPublic();

        $this->out('Компоненты обновлены');

    }

}