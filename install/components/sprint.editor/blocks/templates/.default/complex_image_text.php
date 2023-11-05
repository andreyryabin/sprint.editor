<?php 
/**
 * @var $block array
 * @var $this SprintEditorBlocksComponent
 */
?><div class="sp-complex sp-complex_image_text">
<div class="sp-complex-table"><div class="sp-complex-row">
<div class="sp-complex-col">
<?php $this->includeBlock($block['image']);?>
</div>
<div class="sp-complex-col">
<?php $this->includeBlock($block['text']);?>
</div>
</div></div>
</div>