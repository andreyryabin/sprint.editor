<?php /** @var $block array */ ?><?php

$settings = !empty($block['settings']) ? $block['settings'] : [];
$displayElemens = !empty($settings['display_elements']);

?><?php if ($displayElemens) {
    $elements = Sprint\Editor\Blocks\IblockSections::getElements($block); ?>
    <div class="sp-iblock-elements">
        <ul>
            <?php foreach ($elements as $aItem) { ?>
                <li><a href="<?= $aItem['DETAIL_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a></li>
            <?php } ?>
        </ul>
    </div>
<?php } else {
    $sections = Sprint\Editor\Blocks\IblockSections::getList($block); ?>
    <div class="sp-iblock-sections">
        <ul>
            <?php foreach ($sections as $aItem) { ?>
                <li><a href="<?= $aItem['SECTION_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
