<?php /**
 * @var $this   SprintEditorBlocksComponent
 * @var $layout array
 *
 * Если сетка состоит из 1 колонки без оформления, выводим простой список блоков
 * Иначе выводим нормальный шаблон сетки
 * Переделайте этот шаблон на свое усмотрение
 */
$isSimpleGrid = (count($layout['columns']) == 1 && empty($layout['columns'][0]['css']));
?>
<?php if ($isSimpleGrid) { ?>
    <?php foreach ($layout['columns'] as $column) { ?>
        <?php foreach ($column['blocks'] as $block) { ?>
            <?php $this->includeBlock($block) ?>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <div class="sp-container">
        <div class="row">
            <?php foreach ($layout['columns'] as $column) { ?>
                <div<?php if (!empty($column['css'])) { ?> class="<?= $column['css'] ?>"<?php } ?>>
                    <?php foreach ($column['blocks'] as $block) { ?>
                        <?php $this->includeBlock($block) ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
