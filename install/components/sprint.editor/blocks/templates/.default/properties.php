<? /** @var $block array */ ?>
<table class="sp-properties">
    <? foreach ($block['elements'] as $item): ?>
        <tr>
            <td class="sp-properties_title"><?= $item['title'] ?></td>
            <td class="sp-properties_text"><?= $item['text'] ?></td>
        </tr>
    <? endforeach; ?>
</table>
