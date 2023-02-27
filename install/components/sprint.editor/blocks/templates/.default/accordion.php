<?php /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?>
<div class="sp-accordion">
    <?php foreach ($block['items'] as $item) { ?>
        <div class="sp-accordion-title">
            <?= $item['title'] ?>
        </div>
        <div class="sp-accordion-container">
            <?php foreach ($item['blocks'] as $itemblock) { ?>
                <?php $this->includeBlock($itemblock) ?>
            <?php } ?>
        </div>
    <?php } ?>
</div>
