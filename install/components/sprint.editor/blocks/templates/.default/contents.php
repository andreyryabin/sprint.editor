<?/** @var $block array */?>
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