<?php /**
 * Подключается перед выводом всех блоков
 * @var $this \SprintEditorBlocksComponent
 * @var $blocks array - массив со всеми блоками, можно модифицировать
 * @var $arParams array - массив с параметрами компонента
 */

if ($this->arParams['USE_JQUERY'] == 'Y') {
    \CUtil::InitJSCore(Array("jquery"));
}

if ($this->arParams['USE_FANCYBOX'] == 'Y') {
    $this->registerCss('/bitrix/admin/sprint.editor/assets/fancybox3/jquery.fancybox.min.css');
    $this->registerJs('/bitrix/admin/sprint.editor/assets/fancybox3/jquery.fancybox.min.js');
}

$this->registerCss($this->findResource('_style.css'));
$this->registerJs($this->findResource('_script.js'));
