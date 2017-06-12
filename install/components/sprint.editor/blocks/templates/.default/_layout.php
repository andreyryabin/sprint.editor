<? /**
 * @var $component \SprintEditorBlocksComponent
 * @var $columns array
 *
 */ ?>
<?if (count($columns) == 1 && empty($columns[0])): ?>
    <? $component->includeLayoutBlocks( 0) ?>
<? else: ?>
    <div class="row show-grid">
        <? foreach ($columns as $columnIndex => $columnCss):?>
            <div class="<?= $columnCss ?>"><? $component->includeLayoutBlocks($columnIndex) ?></div>
        <? endforeach; ?>
    </div>
<? endif ?>