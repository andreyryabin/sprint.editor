<?php

use Bitrix\Main\UI\Extension;

/** @global $APPLICATION CMain */
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_TRASH_FILES'));
$APPLICATION->AddHeadScript('/bitrix/admin/sprint.editor/assets/sprint_stepper.js');

$request = Bitrix\Main\Context::getCurrent()->getRequest();

Extension::load("ajax");
Extension::load("ui.buttons");
Extension::load("ui.alerts");

?>
<div id="sprint_stepper" style="width: 50%"></div>
<script type="text/javascript">
    BX.ready(function () {
        sprint_stepper('sprint:editor.controller.cleaner');
    });
</script>


