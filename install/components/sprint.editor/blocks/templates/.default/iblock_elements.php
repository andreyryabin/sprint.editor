<?php /** @var $block array */ ?><?php
$elements = Sprint\Editor\Blocks\IblockElements::getList(
    $block, [
        'NAME',
        'DETAIL_PAGE_URL',
    ]
);
?>
<div class="sp-iblock-elements">
    <?php foreach ($elements as $aItem) { ?>
        <div>
            <a href="<?= $aItem['DETAIL_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a> <br/>
        </div>
    <?php } ?>
</div>
