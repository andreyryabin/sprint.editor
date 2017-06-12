<? /**
 * @var $columns
 * @var $layoutIndex
 * @var $showGrid
 * @var $component \SprintEditorBlocksComponent
 *
 */ ?>
<? if (count($columns) == 1 && empty($columns[0])): ?>
    <? $component->includeLayoutBlocks($layoutIndex, 0) ?>
<? else: ?>
    <div class="row show-grid">
        <? foreach ($columns as $columnIndex => $column): $cssClass = $component->getColumnCss($column); ?>
            <div class="<?= $cssClass ?>"><? $component->includeLayoutBlocks($layoutIndex, $columnIndex) ?></div>
        <? endforeach; ?>
    </div>
<? endif ?>

