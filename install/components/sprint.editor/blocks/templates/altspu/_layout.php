<? /**
 * @var $this \SprintEditorBlocksComponent
 * @var $columns array
 *
 */ ?>
<?if (count($columns) == 1 && empty($columns[0])): ?>
    <? $this->includeLayoutBlocks( 0) ?>
<? else: ?>
    <div class="row">
        <? foreach ($columns as $columnIndex => $columnCss):?>
            <div<?if (!empty($columnCss)):?> class="<?= $columnCss ?>"<?endif?>><? $this->includeLayoutBlocks($columnIndex) ?></div>
        <? endforeach; ?>
    </div>
<? endif ?>