<?php /**
 * Подключается перед выводом всех блоков
 * @var $this \SprintEditorBlocksComponent
 * @var $blocks array - массив со всеми блоками, можно модифицировать
 * @var $arParams array - массив с параметрами компонента
 */

if ($this->arParams['USE_JQUERY'] == 'Y') {
    $this->registerJs('/bitrix/admin/sprint.editor/assets/jquery-1.11.1.min.js');
}

if ($this->arParams['USE_FANCYBOX'] == 'Y') {
    $this->registerCss('/bitrix/admin/sprint.editor/assets/fancybox3/jquery.fancybox.min.css');
    $this->registerJs('/bitrix/admin/sprint.editor/assets/fancybox3/jquery.fancybox.min.js');
}
