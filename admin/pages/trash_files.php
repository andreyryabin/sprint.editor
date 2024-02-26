<?php

use Bitrix\Main\Page\Asset;

/** @global $APPLICATION CMain */
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_TRASH_FILES'));

CJSCore::init(['ajax', 'ui.buttons', 'ui.alerts']);
Asset::getInstance()->addJs('/bitrix/admin/sprint.editor/assets/sprint_stepper.js');

$request = Bitrix\Main\Context::getCurrent()->getRequest();

?>
<div class="adm-detail-content" style="padding: 0">
    <div class="adm-detail-content-item-block">
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


