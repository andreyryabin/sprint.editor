<? /** @var $block array */ ?><?
$elements = Sprint\Editor\Blocks\IblockElements::getList(
    $block, [
    'NAME',
    'DETAIL_PAGE_URL',
]
);
?>
<div class="sp-iblock-elements">
    <? foreach ($elements as $aItem): ?>
        <div>
            <a href="<?= $aItem['DETAIL_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a> <br/>
        </div>
    <? endforeach; ?>
</div>
