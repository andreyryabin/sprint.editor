<?php /** @var $block array */ ?>
<table class="sp-properties">
    <?php foreach ($block['elements'] as $item) { ?>
        <tr>
            <td class="sp-properties_title"><?= htmlspecialcharsbx($item['title']) ?></td>
            <td class="sp-properties_text"><?= htmlspecialcharsbx($item['text']) ?></td>
        </tr>
    <?php } ?>
</table>
