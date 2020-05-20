<? /** @var $block array */ ?>
<? if (!empty($block['title']) && !empty($block['url'])): ?>
    <a class="sp-button_link" <? if (!empty($block['target'])): ?>target="<?= $block['target'] ?>" <? endif; ?> href="<?= $block['url'] ?>"><?= $block['title'] ?></a>
<? endif; ?>
