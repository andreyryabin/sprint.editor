<? /** @var $block array */ ?>
<div class="sp-block-table">
    <table>
        <?foreach ($block['rows'] as $cols):?>
        <tr>
            <?foreach ($cols as $col): $col = Sprint\Editor\Blocks\Table::prepareColumn($col);?>
                <td <?if ($col['style']):?>style="<?=$col['style']?>"<?endif;?>
                    <?if ($col['colspan']):?>colspan="<?=$col['colspan']?>"<?endif;?>
                    <?if ($col['rowspan']):?>rowspan="<?=$col['rowspan']?>"<?endif;?>
                ><?=$col['text']?></td>
            <?endforeach;?>
        </tr>
        <?endforeach;?>
    </table>
</div>