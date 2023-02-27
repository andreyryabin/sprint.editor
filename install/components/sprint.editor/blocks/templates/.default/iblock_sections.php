<?php /** @var $block array */ ?><?php
$sections = Sprint\Editor\Blocks\IblockSections::getList(
    $block, [
        'NAME',
        'SECTION_PAGE_URL',
    ]
);
?>
<div class="sp-iblock-sections">
    <?php foreach ($sections as $aItem) { ?>
        <div>
            <a href="<?= $aItem['SECTION_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a> <br/>
        </div>
    <?php } ?>
</div>
