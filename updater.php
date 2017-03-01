<?php
/** @var CUpdater $updater */

if ($updater && $updater instanceof \CUpdater) {
    $updater->CopyFiles("install/components/", "components/" );
    $updater->CopyFiles("install/admin/", "admin/" );

    //тут нельзя использовать классы модуля, так как их может не быть в обновлении

    //1.0.11

}
