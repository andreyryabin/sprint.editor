<?php
/** @global $APPLICATION CMain */

use Bitrix\Main\Page\Asset;

global $APPLICATION;
$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_SUPPORT'));
Asset::getInstance()->addJs('/bitrix/admin/sprint.editor/assets/support_page.js');
?>
<div class="sp-support">
    На этой странице можно поддержать улучшения, предложенные пользователями редактора, которые вы хотели бы видеть в обновлениях.
    <br>
    Именно благодаря активности участников в редакторе появилось большинство существующих блоков, настроек и возможностей.
    <br>
    Примеры улучшений на вкладке <a href="https://marketplace.1c-bitrix.ru/solutions/sprint.editor/#tab-log-link" target="_blank">Что нового</a>
</div>
<div class="sp-support">
    Предложить свою идею по улучшению можно в <a href="https://t.me/sprint_editor" target="_blank">telegram-группе</a>
    или на вкладке <a href="https://marketplace.1c-bitrix.ru/solutions/sprint.editor/#tab-comments-link" target="_blank">Обсуждения</a>
    <br>
    Появление вашей идеи в списке ниже означает, что она реализуема, полезна и взята в работу.
    <br>
    После завершения сбора она появится в ближайшем обновлении модуля.
</div>
<div class="sp-support sp-table">
    <div class="sp-row">
        <div class="sp-col sp-col-tabs">
            <div class="sp-support-link">
                <strong>Отдельный раздел для статей</strong>
                <div>
                    Разработка в админке отдельного раздела со статьями,
                    новый тип свойства у инфоблоков "привязка к статье".
                </div>
            </div>
            <div class="sp-support-link">
                <strong>Улучшение конструктора блоков</strong>
                <div>
                    Удаление блоков, перемещение шаблонов, создание настроек,
                    именованные названия блоков, документация.
                </div>
            </div>
            <div class="sp-support-link">
                <strong>Обновление модуля</strong>
                <div>
                    Поддержать ближайшее обновление модуля
                </div>
            </div>
        </div>
        <div class="sp-col sp-col-contents">
            <div class="sp-support-content">
                <iframe src="https://yoomoney.ru/quickpay/fundraise/widget?billNumber=3Nh3BQRLQqg.231129" width="500" height="480" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
            </div>
            <div class="sp-support-content">
                <iframe src="https://yoomoney.ru/quickpay/fundraise/widget?billNumber=S6kHngOGM5A.231130&" width="500" height="480" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
            </div>
            <div class="sp-support-content">
                <iframe src="https://yoomoney.ru/quickpay/fundraise/widget?billNumber=12KAE0J04Q3.240509&" width="500" height="480" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
            </div>
        </div>
    </div>
</div>
