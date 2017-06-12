# Редактор для контент-менеджеров #
* Платформа: 1С-Битрикс
* Маркетплейс: [http://marketplace.1c-bitrix.ru/solutions/sprint.editor/](http://marketplace.1c-bitrix.ru/solutions/sprint.editor/)
* Документация: [https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home)

Пример подключения в шаблоне компонента bitrix:news.detail

```
<?$APPLICATION->IncludeComponent(
    "sprint.editor:blocks",
    ".default",
    Array(
        "ELEMENT_ID" => $arResult['ID'],
        "IBLOCK_ID" => $arResult['IBLOCK_ID'],
        "PROPERTY_CODE" => "EDITOR",
        "USE_JQUERY" => "Y",
        "USE_FANCYBOX" => "Y",
    ),
    $component,
    Array(
        'HIDE_ICONS' => 'Y'
    )
);?>

```

![sprint-editor-icon.jpg](https://bitbucket.org/repo/adr668/images/1541013359-sprint-editor-icon.jpg)