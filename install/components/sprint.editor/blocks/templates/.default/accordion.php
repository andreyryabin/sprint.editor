<? /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?>
<div class="sp-accordion">
    <? foreach ($block['items'] as $item): ?>
        <div class="sp-accordion-title">
            <?= $item['title'] ?>
        </div>
        <div class="sp-accordion-container">
            <? foreach ($item['blocks'] as $itemblock): ?>
                <? $this->includeBlock($itemblock) ?>
            <? endforeach; ?>
        </div>
    <? endforeach; ?>
</div>
