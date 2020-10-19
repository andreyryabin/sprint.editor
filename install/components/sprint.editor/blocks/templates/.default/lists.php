<? /** @var $block array */

$tag = !empty($block['type']) ? $block['type'] : 'ul';
?>
<<?= $tag ?> class="sp-lists">
<? foreach ($block['elements'] as $item) { ?>
    <li><?= $item['text'] ?></li>
<? } ?>
</<?= $tag ?>>
