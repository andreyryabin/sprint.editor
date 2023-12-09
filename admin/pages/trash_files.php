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
<div class="adm-detail-content" style="padding: 0">
    <div class="adm-detail-content-item-block" style="width: 50%">
        <div class="adm-info-message" style="margin:0 0 15px">
            <?= nl2br(GetMessage('SPRINT_EDITOR_TRASH_FILES_DESC')) ?>
        </div>
        <div id="sprint_stepper"></div>
    </div>
</div>

<script type="text/javascript">
    BX.ready(function () {
        sprint_stepper('sprint:editor.controller.cleaner');
    });
</script>


