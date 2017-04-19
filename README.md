# Редактор для контент-менеджеров #
* Платформа: 1С-Битрикс
* Маркетплейс: [http://marketplace.1c-bitrix.ru/solutions/sprint.editor/](http://marketplace.1c-bitrix.ru/solutions/sprint.editor/)
* Документация: [https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home)

Пример подключения в шаблоне компонента bitrix:news.detail

```
<?$APPLICATION->IncludeComponent("sprint.editor:blocks", ".default", array(
    'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    'ELEMENT_ID' => $arResult['ID'],
    'PROPERTY_CODE' => 'EDITOR',
),false,array(
    'HIDE_ICONS' => 'Y'
))?>

```