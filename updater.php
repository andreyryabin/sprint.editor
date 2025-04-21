<?php
/** @var CUpdater $updater */

if ($updater && $updater instanceof CUpdater) {
    //тут нельзя использовать классы модуля, так как их может не быть в обновлении

    if (!function_exists('sprint_editor_rmdir')) {
        function sprint_editor_rmdir($dir)
        {
            if (!is_dir($dir)) {
                return false;
            }
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? sprint_editor_rmdir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
    }

    $pathsToRemove = [

    ];

    $filesToRemove = [

    ];

    foreach ($pathsToRemove as $path) {
        sprint_editor_rmdir($_SERVER['DOCUMENT_ROOT'] . $path);
    }

    foreach ($filesToRemove as $file) {
        if (is_file($_SERVER['DOCUMENT_ROOT'] . $file)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $file);
        }
    }

    if (is_dir(__DIR__ . '/install/components/')) {
        $updater->CopyFiles("install/components/", "components/");
    }

    if (is_dir(__DIR__ . '/install/admin/')) {
        $updater->CopyFiles("install/admin/", "admin/");
    }
    //4.22.0

}
