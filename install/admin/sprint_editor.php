<?php

if (is_file($_SERVER["DOCUMENT_ROOT"] . "/local/modules/sprint.editor/admin/sprint_editor.php")) {
    /** @noinspection PhpIncludeInspection */
    require($_SERVER["DOCUMENT_ROOT"] . "/local/modules/sprint.editor/admin/sprint_editor.php");
} else {
    /** @noinspection PhpIncludeInspection */
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sprint.editor/admin/sprint_editor.php");
}
