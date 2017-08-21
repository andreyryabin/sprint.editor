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

    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/schema/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/complex/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/iblock/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/medialib/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/sprint.editor/blocks/');


    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sprint.editor/install/admin/sprint.editor/schema/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sprint.editor/install/admin/sprint.editor/complex/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sprint.editor/install/admin/sprint.editor/iblock/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sprint.editor/install/admin/sprint.editor/medialib/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sprint.editor/install/admin/sprint.editor/blocks/');


    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/sprint.editor/blocks/templates/schema/');
    sprint_remove_directory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sprint.editor/install/components/sprint.editor/blocks/templates/schema/');



    if (is_dir(__DIR__ . '/install/components/')){
        $updater->CopyFiles("install/components/", "components/" );
    }

    if (is_dir(__DIR__ . '/install/admin/')){
        $updater->CopyFiles("install/admin/", "admin/" );
    }

    //1.1.5

}
