# Редактор для контент-менеджеров #
* Платформа: 1С-Битрикс
* Маркетплейс: [http://marketplace.1c-bitrix.ru/solutions/sprint.editor/](http://marketplace.1c-bitrix.ru/solutions/sprint.editor/)
* Документация: [https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home)
* Группа в телеграм: [https://t.me/sprint_editor](https://t.me/sprint_editor)
* Поддержать разработку [https://money.yandex.ru/to/410012104240288/500](https://money.yandex.ru/to/410012104240288/500)

![sprint-editor-icon.jpg](https://bitbucket.org/repo/adr668/images/1541013359-sprint-editor-icon.jpg)


### Примеры подключения

Пример подключения редактора в шаблоне компонента bitrix:news.detail
```
<?$APPLICATION->IncludeComponent(
    "sprint.editor:blocks",
    ".default",
    Array(
        "ELEMENT_ID" => $arResult["ID"],
        "IBLOCK_ID" => $arResult["IBLOCK_ID"],
        "PROPERTY_CODE" => "EDITOR1",
    ),
    $component,
    Array(
        "HIDE_ICONS" => "Y"
    )
);?>

```

Пример подключения редактора как включаемой области в произвольном месте сайта.

Создадим инфоблок с включаемыми областями, например
с кодом include_areas и типом content, создадим в инфоблоке свойство с редактором с EDITOR1, 
создадим элемент с кодом common_information_1, заполним и выведем его в произвольном месте сайта

параметр SHOW_AREAS - позволит отобразить битриксовую панельку с возможностью редактирования этой области

```
#!php

<? $APPLICATION->IncludeComponent("sprint.editor:blocks", ".default", [
    'IBLOCK_TYPE' => 'content',
    'IBLOCK_CODE' => 'include_areas',
    'ELEMENT_CODE' => 'common_information_1',
    'PROPERTY_CODE' => 'EDITOR1',
    'SHOW_AREAS' => 'Y',
]); ?>
``` 

События
-------

### OnGetSearchIndex

```php
/**
 * <sprint.editor::OnGetSearchIndex> - функция обработчик события OnGetSearchIndex модуля sprint.editor
 *
 * @param array $propertyValue Значение свойства. 
 * @param string $currentValue Текущее представление значения свойства для модуля поиска. 
 *
 * @return string Изменённое представление значения свойства для модуля поиска.
 */
<sprint.editor::OnGetSearchIndex>(array $propertyValue, string $currentValue): string
```

Событие происходит при формировании представления значения свойства для индексации модулем поиска как для типа свойства
элемента инфоблока "Редактор блоков (sprint.editor)", так и для одноимённого типа пользовательского свойства.  
