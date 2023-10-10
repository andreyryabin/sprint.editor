<?php /** @var $block array */ ?><?php
$elements = Sprint\Editor\Blocks\IblockElements::getList($block);
?>
<div class="sp-iblock-elements">
    <?php foreach ($elements as $aItem) { ?>
        <ul>
            <li><a href="<?= $aItem['DETAIL_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a></li>
        </ul>
    <?php } ?>
</div>
