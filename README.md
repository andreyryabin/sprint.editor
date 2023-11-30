# Редактор для контент-менеджеров #

[![Latest Stable Version](https://poser.pugx.org/andreyryabin/sprint.editor/v/stable.svg)](https://packagist.org/packages/andreyryabin/sprint.editor/)
[![Total Downloads](https://img.shields.io/packagist/dt/andreyryabin/sprint.editor.svg?style=flat)](https://packagist.org/packages/andreyryabin/sprint.editor)

* Платформа: 1С-Битрикс
* Маркетплейс: [http://marketplace.1c-bitrix.ru/solutions/sprint.editor/](http://marketplace.1c-bitrix.ru/solutions/sprint.editor/)
* Документация: [https://github.com/andreyryabin/sprint.editor/wiki](https://github.com/andreyryabin/sprint.editor/wiki)
* Группа в телеграм: [https://t.me/sprint_editor](https://t.me/sprint_editor)

  
![sprint-editor-icon.jpg](https://bitbucket.org/repo/adr668/images/1541013359-sprint-editor-icon.jpg)



Особая благодарность
-------------------------
Самой лучшей IDE на свете!\
[![Phpstorm](https://raw.githubusercontent.com/wiki/andreyryabin/sprint.migration/assets/phpstorm.png)](https://www.jetbrains.com/?from=sprint.migration)

А также всем помощникам!\
[https://github.com/andreyryabin/sprint.editor/blob/master/contributors.txt](https://github.com/andreyryabin/sprint.editor/blob/master/contributors.txt)


Установка через composer
-------------------------
Пример вашего composer.json с установкой модуля в bitrix/modules/ 
с копированием скриптов админки в bitrix/admin/sprint.editor/
и компонента редактора для публичной части в bitrix/components/sprint.editor/blocks/

```
{
  "extra": {
    "copy-file": {
      "bitrix/modules/sprint.editor/install/admin/": "bitrix/admin/",
      "bitrix/modules/sprint.editor/install/components/": "bitrix/components/"
    },
    "installer-paths": {
      "bitrix/modules/{$name}/": ["type:bitrix-module"]
    }
  },
  "require": {
    "andreyryabin/sprint.editor": "*",
    "slowprog/composer-copy-file": "~0.3"
  },
  "scripts": {
    "post-install-cmd": [
      "SlowProg\\CopyFile\\ScriptHandler::copy"
    ],
    "post-update-cmd": [
      "SlowProg\\CopyFile\\ScriptHandler::copy"
    ]
  },
  "config": {
    "allow-plugins": {
      "composer/installers": true
    }
  }
}
```
