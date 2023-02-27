<?php /** @var $block array */ ?>
<table class="sp-properties">
    <?php foreach ($block['elements'] as $item) { ?>
        <tr>
            <td class="sp-properties_title"><?= $item['title'] ?></td>
            <td class="sp-properties_text"><?= $item['text'] ?></td>
        </tr>
    <?php } ?>
</table>
