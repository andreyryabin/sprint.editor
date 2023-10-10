<?php /** @var $block array */ ?><?php
$elements = Sprint\Editor\Blocks\IblockElements::getList($block);
?>
<div class="sp-iblock-elements">
    <ul>
        <?php foreach ($elements as $aItem) { ?>
            <li><a href="<?= $aItem['DETAIL_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a></li>
        <?php } ?>
    </ul>
</div>
