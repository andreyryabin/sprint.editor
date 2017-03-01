# Редактор для контент-менеджеров для 1С-Битрикс #

Подключение в шаблоне компонента bitrix:news.detail

```
<?$APPLICATION->IncludeComponent("sprint.editor:blocks", ".default", array(
    'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    'ELEMENT_ID' => $arResult['ID'],
    'PROPERTY_CODE' => 'EDITOR',
),false,array(
    'HIDE_ICONS' => 'Y'
))?>

```



[https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home)