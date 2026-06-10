<?php /** @var $block array */
/** @var $this SprintEditorBlocksComponent */

$elements = Sprint\Editor\Blocks\contents::getElements($block, $this);

?>
<div class="sp-contents">
    <div class="sp-contents_title"><?= GetMessage('SPRINT_EDITOR_block_contents_title') ?></div>
    <ul class="sp-contents_elements">
        <?php foreach ($elements as $item) {
            $cssclass = htmlspecialcharsbx('level' . $item['level']);
            $margin = (intval($item['level']) - 1) * 40;
            ?>
            <li class="<?= $cssclass ?>" style="margin-left:<?= $margin ?>px;"><a href="#<?= htmlspecialcharsbx($item['anchor']) ?>"><?= htmlspecialcharsbx($item['text']) ?></a></li>
        <?php } ?>
    </ul>
</div>
