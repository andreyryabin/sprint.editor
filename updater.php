<?php
/** @var CUpdater $updater */

if ($updater && $updater instanceof CUpdater) {
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

    $paths = [
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/complex_image_text/',
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/complex_video_text/',
        '/bitrix/admin/sprint.editor/blocks/complex_image_text/',
        '/bitrix/admin/sprint.editor/blocks/complex_video_text/',
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/coub/',
        '/bitrix/admin/sprint.editor/blocks/coub/',
    ];

    foreach ($paths as $path) {
        sprint_editor_rmdir($_SERVER['DOCUMENT_ROOT'] . $path);
    }

    if (is_dir(__DIR__ . '/install/components/')) {
        //$updater->CopyFiles("install/components/", "components/");
    }

    if (is_dir(__DIR__ . '/install/admin/')) {
        //$updater->CopyFiles("install/admin/", "admin/");
    }
    //4.5.3

}
