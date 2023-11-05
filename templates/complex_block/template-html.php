<?php
/**
 * @var $blockName
 * @var $layouts
 */
?><?php foreach ($layouts as $lindex => $layout) { ?>
    <?php if (!empty($layout['title'])) { ?>
        <div class="sp-x-box-caption"><?= $layout['title'] ?></div>
    <?php } ?>
    <div class="sp-table sp-table-fixed">
        <div class="sp-row">
            <?php foreach ($layout['columns'] as $cindex => $column) { ?>
                <div class="sp-col">
                    <?php foreach ($column['blocks'] as $cblock) { ?>
                        <div class="sp-area <?= $cblock['areaclass'] ?>"></div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>

