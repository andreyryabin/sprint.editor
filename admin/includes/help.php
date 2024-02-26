<?php

use Sprint\Editor\Module;

global $APPLICATION;
$isSettinsPage = strpos($APPLICATION->GetCurPage(), 'settings.php');
?>
<div class="sp-help sp-table">
    <div class="sp-row">
        <div class="sp-col sp-col-left">
            <div class="sp-help-text">
                <?= GetMessage('SPRINT_EDITOR_MODULE_VERSION') ?>: <?= Module::getVersion() ?>
            </div>
            <div class="sp-help-text">
                <?php if ($isSettinsPage): ?>
                    <a href="/bitrix/admin/sprint_editor.php?showpage=support&lang=<?= LANGUAGE_ID ?>"><?= GetMessage('SPRINT_EDITOR_SUPPORT') ?></a>
                <?php else: ?>
                    <a href="/bitrix/admin/settings.php?mid=<?= Module::ID ?>&mid_menu=1&lang=<?= LANGUAGE_ID ?>"><?= GetMessage('SPRINT_EDITOR_OPTIONS') ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="sp-col sp-col-right">
            <div class="sp-help-text">
                <?= GetMessage('SPRINT_EDITOR_HELP_MARKETPLACE') ?>
                <br/>
                <a target="_blank" href="https://marketplace.1c-bitrix.ru/solutions/sprint.editor/">
                    https://marketplace.1c-bitrix.ru/solutions/sprint.editor/
                </a>
            </div>
            <div class="sp-help-text">
                <?= GetMessage('SPRINT_EDITOR_HELP_COMPOSER') ?>
                <br/>
                <a href="https://packagist.org/packages/andreyryabin/sprint.editor" target="_blank">
                    https://packagist.org/packages/andreyryabin/sprint.editor
                </a>
            </div>
            <div class="sp-help-text">
                <?= GetMessage('SPRINT_EDITOR_HELP_WIKI') ?>
                <br/>
                <a href="https://github.com/andreyryabin/sprint.editor/wiki" target="_blank">
                    https://github.com/andreyryabin/sprint.editor/wiki
                </a>
            </div>
            <div class="sp-help-text">
                <?= GetMessage('SPRINT_EDITOR_HELP_TELEGRAM') ?>
                <br/>
                <a href="https://t.me/sprint_editor" target="_blank">
                    https://t.me/sprint_editor
                </a>
            </div>
        </div>
    </div>
</div>
