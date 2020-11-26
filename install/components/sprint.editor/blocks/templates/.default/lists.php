<? /** @var $block array */

$settings = !empty($block['settings']) ? $block['settings'] : [];
$tag = !empty($settings['type']) ? $settings['type'] : 'ul';

?><<?= $tag ?> class="sp-lists">
<? foreach ($block['elements'] as $item) { ?>
    <li><?= $item['text'] ?></li>
<? } ?>
</<?= $tag ?>>
