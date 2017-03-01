<?/** @var $block array */?><?
$elements = Sprint\Editor\Blocks\IblockElements::getList($block, array(
    'NAME',
    'DETAIL_PAGE_URL'
));
?><div class="c-iblock-elements" style="width: 350px;background: #ddd;padding: 10px;">
    <?foreach ($elements as $aItem):?>
        <div>
            <a href="<?=$aItem['DETAIL_PAGE_URL']?>"><?=$aItem['NAME']?></a> <br/>
        </div>
    <?endforeach;?>
</div>