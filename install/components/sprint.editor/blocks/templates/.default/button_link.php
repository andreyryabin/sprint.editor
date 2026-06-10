<?php /** @var $block array */ ?>
<?php if (!empty($block['title']) && !empty($block['url']) && preg_match('#^(https?:|mailto:|/)#i', (string)$block['url'])) { ?>
    <a class="sp-button_link" <?php if (!empty($block['target'])){ ?>target="<?= htmlspecialcharsbx($block['target']) ?>" <?php } ?> href="<?= htmlspecialcharsbx($block['url']) ?>"><?= htmlspecialcharsbx($block['title']) ?></a>
<?php } ?>
