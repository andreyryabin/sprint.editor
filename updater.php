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
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/complex_image_text/',
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/complex_video_text/',
        '/bitrix/admin/sprint.editor/blocks/complex_image_text/',
        '/bitrix/admin/sprint.editor/blocks/complex_video_text/',
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/coub/',
        '/bitrix/admin/sprint.editor/blocks/coub/',
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/slideshare/',
        '/bitrix/admin/sprint.editor/blocks/slideshare/',
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/dump/',
        '/bitrix/admin/sprint.editor/blocks/dump/',
        '/bitrix/modules/sprint.editor/admin/includes/',
        '/bitrix/modules/sprint.editor/classes/',
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/assets/jquery-ui-1.12.1.custom/',
        '/bitrix/admin/sprint.editor/assets/jquery-ui-1.12.1.custom/',
    ];

    $filesToRemove = [
        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/assets/jquery-1.11.1.min.js',
        '/bitrix/admin/sprint.editor/assets/jquery-1.11.1.min.js',

        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/assets/sprint_editor_full.js',
        '/bitrix/admin/sprint.editor/assets/sprint_editor_full.js',

        '/bitrix/modules/sprint.editor/install/components/sprint.editor/blocks/templates/.default/coub.php',
        '/bitrix/components/sprint.editor/blocks/templates/.default/coub.php',
        '/bitrix/modules/sprint.editor/install/components/sprint.editor/blocks/templates/.default/slideshare.php',
        '/bitrix/components/sprint.editor/blocks/templates/.default/slideshare.php',

        '/bitrix/modules/sprint.editor/templates/admin_editor_simple.php',

        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/assets/sprint_editor_simple.js',
        '/bitrix/admin/sprint.editor/assets/sprint_editor_simple.js',

        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/box/layout-col.html',
        '/bitrix/admin/sprint.editor/blocks/box/layout-col.html',

        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/box/layout-col-settings.html',
        '/bitrix/admin/sprint.editor/blocks/box/layout-col-settings.html',

        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/box/layout-col-tab.html',
        '/bitrix/admin/sprint.editor/blocks/box/layout-col-tab.html',

        '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/box/select-pack.html',
        '/bitrix/admin/sprint.editor/blocks/box/select-pack.html',
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
        //$updater->CopyFiles("install/components/", "components/");
    }

    if (is_dir(__DIR__ . '/install/admin/')) {
        $updater->CopyFiles("install/admin/", "admin/");
    }

    //4.6.1

}
