# Редактор для контент-менеджеров #
* Платформа: 1С-Битрикс
* Маркетплейс: [http://marketplace.1c-bitrix.ru/solutions/sprint.editor/](http://marketplace.1c-bitrix.ru/solutions/sprint.editor/)
* Документация: [https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home](https://bitbucket.org/andrey_ryabin/sprint.editor/wiki/Home)

![sprint-editor-icon.jpg](https://bitbucket.org/repo/adr668/images/1541013359-sprint-editor-icon.jpg)


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
