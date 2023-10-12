<?php
/** @var CUpdater $updater */

if ($updater && $updater instanceof \CUpdater) {
    //тут нельзя использовать классы модуля, так как их может не быть в обновлении

    if (!function_exists('sprint_editor_rmdir')) {
        function sprint_editor_rmdir($dir)
        {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? sprint_editor_rmdir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
    }

    //sprint_editor_rmdir(__DIR__ . '/install/classes/');

    if (is_dir(__DIR__ . '/install/components/')) {
        //$updater->CopyFiles("install/components/", "components/");
    }

    if (is_dir(__DIR__ . '/install/admin/')) {
        //$updater->CopyFiles("install/admin/", "admin/");
    }
    //4.5.3

}
