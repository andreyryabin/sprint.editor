<?php
/** @var CUpdater $updater */

if ($updater && $updater instanceof \CUpdater) {

    //тут нельзя использовать классы модуля, так как их может не быть в обновлении

    if (!function_exists('sprint_editor_rmdir')) {
        function sprint_editor_rmdir($dir) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? sprint_editor_rmdir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
    }


    //example
    //sprint_editor_rmdir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/freemium/');

    if (is_dir(__DIR__ . '/install/components/')) {
        $updater->CopyFiles("install/components/", "components/");
    }

    if (is_dir(__DIR__ . '/install/admin/')) {
        $updater->CopyFiles("install/admin/", "admin/");
    }

    //2.3.9

}
