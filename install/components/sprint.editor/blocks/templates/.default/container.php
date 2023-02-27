<?php /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?>
<div class="sp-block-container">
    <?php foreach ($block['blocks'] as $itemblock) { ?>
        <?php $this->includeBlock($itemblock) ?>
    <?php } ?>
</div>
