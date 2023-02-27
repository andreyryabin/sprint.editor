<?php /** @var $block array */ ?>
<div class="sp-block-table">
    <table>
        <?php foreach ($block['rows'] as $cols) { ?>
            <tr>
                <?php foreach ($cols as $col) {
                    $col = Sprint\Editor\Blocks\Table::prepareColumn($col); ?>
                    <td <?php if ($col['style']){ ?>style="<?= $col['style'] ?>"<?php } ?>
                        <?php if ($col['colspan']){ ?>colspan="<?= $col['colspan'] ?>"<?php } ?>
                        <?php if ($col['rowspan']){ ?>rowspan="<?= $col['rowspan'] ?>"<?php } ?>
                    ><?= $col['text'] ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
</div>
