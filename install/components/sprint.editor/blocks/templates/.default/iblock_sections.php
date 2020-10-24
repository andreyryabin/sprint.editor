<? /** @var $block array */ ?><?
$sections = Sprint\Editor\Blocks\IblockSections::getList(
    $block, [
        'NAME',
        'SECTION_PAGE_URL',
    ]
);
?>
<div class="sp-iblock-sections">
    <? foreach ($sections as $aItem): ?>
        <div>
            <a href="<?= $aItem['SECTION_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a> <br/>
        </div>
    <? endforeach; ?>
</div>
