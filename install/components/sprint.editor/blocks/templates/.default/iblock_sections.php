<?php /** @var $block array */ ?><?php

$settings = !empty($block['settings']) ? $block['settings'] : [];
$displayElemens = !empty($settings['display_elements']);

?><?php if ($displayElemens) {
    $elements = Sprint\Editor\Blocks\IblockSections::getElements($block); ?>
    <div class="sp-iblock-elements">
        <?php foreach ($elements as $aItem) { ?>
            <ul>
                <li><a href="<?= $aItem['DETAIL_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a></li>
            </ul>
        <?php } ?>
    </div>
<?php } else {
    $sections = Sprint\Editor\Blocks\IblockSections::getList($block); ?>
    <div class="sp-iblock-sections">
        <?php foreach ($sections as $aItem) { ?>
            <ul>
                <li><a href="<?= $aItem['SECTION_PAGE_URL'] ?>"><?= $aItem['NAME'] ?></a></li>
            </ul>
        <?php } ?>
    </div>
<?php } ?>
