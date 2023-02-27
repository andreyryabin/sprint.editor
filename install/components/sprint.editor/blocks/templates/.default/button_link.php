<?php /** @var $block array */ ?>
<?php if (!empty($block['title']) && !empty($block['url'])) { ?>
    <a class="sp-button_link" <?php if (!empty($block['target'])){ ?>target="<?= $block['target'] ?>" <?php } ?> href="<?= $block['url'] ?>"><?= $block['title'] ?></a>
<?php } ?>
