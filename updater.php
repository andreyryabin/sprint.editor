<?php
/** @var CUpdater $updater */

if ($updater && $updater instanceof \CUpdater) {

    if (is_dir(__DIR__ . '/install/components/')){
        $updater->CopyFiles("install/components/", "components/" );
    }

    if (is_dir(__DIR__ . '/install/admin/')){
        $updater->CopyFiles("install/admin/", "admin/" );
    }

    //тут нельзя использовать классы модуля, так как их может не быть в обновлении

    //отключаем показ блоков микроразметки по умолчанию
    if (\COption::GetOptionString('sprint.editor','enable_blocks_schema', '') != 'yes'){
        \COption::SetOptionString('sprint.editor','enable_blocks_schema','no');
    }


    //1.1.4

}
