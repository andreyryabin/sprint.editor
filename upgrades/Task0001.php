<?php

namespace Sprint\Editor;

class Task0001 extends Upgrade
{

    public function getDescription() {
        return 'Обновить скрипты и компоненты';
    }

    public function execute() {

        /** @var $tmpmodule \sprint_editor */
        $tmpmodule = \CModule::CreateModuleObject('sprint.editor');
        $tmpmodule->afterInstall();

        $this->out('Скрипты и компоненты обновлены');

    }

}