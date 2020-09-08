<? /**
 * @var $this   SprintEditorBlocksComponent
 * @var $layout array
 *
 * Если сетка состоит из 1 колонки без оформления, выводим простой список блоков
 * Иначе выводим нормальный шаблон сетки
 * Переделайте этот шаблон на свое усмотрение
 */
$isSimpleGrid = (count($layout['columns']) == 1 && empty($layout['columns'][0]['css']));
?>
<? if ($isSimpleGrid) { ?>
    <? foreach ($layout['columns'] as $column) { ?>
        <? foreach ($column['blocks'] as $block) { ?>
            <? $this->includeBlock($block) ?>
        <? } ?>
    <? } ?>
<? } else { ?>
    <div class="sp-container">
        <div class="row">
            <? foreach ($layout['columns'] as $column) { ?>
                <div<? if (!empty($column['css'])) { ?> class="<?= $column['css'] ?>"<? } ?>>
                    <? foreach ($column['blocks'] as $block) { ?>
                        <? $this->includeBlock($block) ?>
                    <? } ?>
                </div>
            <? } ?>
        </div>
    </div>
<? } ?>
