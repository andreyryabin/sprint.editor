<? /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */?>
<div class="sp-block-container">
    <? foreach ($block['blocks'] as $itemblock): ?>
        <? $this->includeBlock($itemblock) ?>
    <? endforeach; ?>
</div>
