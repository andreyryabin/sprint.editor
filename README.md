# Редактор для контент-менеджеров (1С-Битрикс) #

* Маркетплейс 1с-Битрикс: [http://marketplace.1c-bitrix.ru/solutions/sprint.editor/](http://marketplace.1c-bitrix.ru/solutions/sprint.editor/)
* Обновления: [http://marketplace.1c-bitrix.ru/solutions/sprint.editor/#tab-log-link](http://marketplace.1c-bitrix.ru/solutions/sprint.editor/#tab-log-link)
* Документация: [https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home)
* Трекер задач: [https://bitbucket.org/andrey_ryabin/sprint.editor/issues/new](https://bitbucket.org/andrey_ryabin/sprint.editor/issues/new)
* Скачать модуль: [https://yadi.sk/d/GN5OicOB3BHGad](https://yadi.sk/d/GN5OicOB3BHGad)
* Варианты подключения компонента: [https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Components](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Components)
* Настройка списка блоков по умолчанию: [https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/iblock-default-editor-settings](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/iblock-default-editor-settings)

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
