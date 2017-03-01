<?php
 /**
  * @var $showSortButtons
  * @var $uniqId
  */
?><div class="sp-box sp-box-{{!it.group.name}} j-box">
    <div class="sp-box_panel j-box_handle">
        {{?it.group.show_title == "yes"}}
        <span class="sp-box_panel-group_title">{{!it.group.title}}.</span>
        {{?}}
        <span class="sp-box_panel-title">{{!it.title}}</span>
        <div class="sp-box_panel-buttons">
        <?if ($showSortButtons == 'yes'):?>
            <a title="Поднять вверх" class="j-upbox" href="#">Вверх</a>
            <a title="Опустить вниз" class="j-dnbox" href="#">Вниз</a>
        <?endif;?>
            <a title="Удалить блок" class="j-delbox" href="#">Удалить</a>
        </div>
    </div>
    <div class="j-box-block"></div>
</div>