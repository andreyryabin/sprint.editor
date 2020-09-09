<? /** @var $block array */ ?>
<? if (!empty($block['title']) && !empty($block['url'])): ?>
    <a class="sp-button_link" 
    target="_blank" href="<?= $block['url'] ?>">
    <?= $block['title'] ?>
    <i class="fa fa-location-arrow" aria-hidden="true"></i>
    </a>
<? endif; ?>
