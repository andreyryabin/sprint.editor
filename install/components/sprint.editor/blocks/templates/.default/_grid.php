<? /**
 * @var $this \SprintEditorBlocksComponent
 * @var $layout array
 *
 */ ?>
<?if (count($layout['columns']) == 1 && empty($layout['columns'][0]['css'])): ?>
    <? $this->includeLayoutBlocks( 0) ?>
<? else: ?>
<div class="container">
    <div class="row">
        <? foreach ($layout['columns'] as $columnIndex => $column):?>
            <div<?if (!empty($column['css'])):?> class="<?= $column['css'] ?>"<?endif?>><? $this->includeLayoutBlocks($columnIndex) ?></div>
        <? endforeach; ?>
    </div>
</div>
<? endif ?>
