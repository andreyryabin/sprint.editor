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

    //1.0.12

}
