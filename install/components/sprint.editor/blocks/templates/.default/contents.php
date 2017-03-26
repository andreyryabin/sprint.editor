<?/** @var $block array */?>
<style>
    .c-contents_elements{ list-style-type:none; counter-reset:list; }
    /* цвет чисел */
    .c-contents_elements li:before{ color:#555; }
    /* уровень 0 */
    .c-contents_elements li.level1{ counter-increment:list; counter-reset:list1; }
    .c-contents_elements li.level1:before{ content:counter(list) '. '; }
    /* уровень 1 */
    .c-contents_elements li.level2{ counter-increment:list1; counter-reset:list2; }
    .c-contents_elements li.level2:before{ content:counter(list) '.' counter(list1) '. '; }
    /* уровень 2 */
    .c-contents_elements li.level3{ counter-increment:list2; }
    .c-contents_elements li.level3:before{ content:counter(list) '.' counter(list1) '.' counter(list2) '. '; }
</style>
<div class="c-contents">
    <div class="c-contents_title">Содержание:</div>
    <ul class="c-contents_elements">
        <?foreach ($block['elements'] as $item):
            $cssclass = 'level' . $item['level'];
            $margin = ($item['level'] - 1) * 40;
        ?>
            <li class="<?=$cssclass?>" style="margin-left:<?=$margin?>px;"><a href="#<?=$item['anchor']?>"><?=$item['text']?></a></li>
        <?endforeach;?>
    </ul>
</div>