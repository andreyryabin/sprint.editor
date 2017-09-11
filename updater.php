<?php
/** @var CUpdater $updater */

if ($updater && $updater instanceof \CUpdater) {

    //тут нельзя использовать классы модуля, так как их может не быть в обновлении

    if (!function_exists('sprint_remove_directory')){
        function sprint_remove_directory($dir) {
            if ($objs = glob($dir."/*")) {
                foreach($objs as $obj) {
                    is_dir($obj) ? sprint_remove_directory($obj) : unlink($obj);
                }
            }
            rmdir($dir);
        }
    }



    //example
    //sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/freemium/');

    if (is_dir(__DIR__ . '/install/components/')){
        $updater->CopyFiles("install/components/", "components/" );
    }

    if (is_dir(__DIR__ . '/install/admin/')){
        $updater->CopyFiles("install/admin/", "admin/" );
    }

    //1.1.8

}
