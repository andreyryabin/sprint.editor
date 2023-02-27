<?php /** @var $block array */ ?><?php

$text = Sprint\Editor\Blocks\Text::getValue($block['text']);
$image = Sprint\Editor\Blocks\Image::getImage(
    $block['image'], [
        'width'  => 320,
        'height' => 240,
        'exact'  => 0,
    ]
);
?>

<div class="sp-image-text">
    <?php if ($image) { ?>
        <img alt="<?= $image['DESCRIPTION'] ?>" src="<?= $image['SRC'] ?>">
    <?php } ?>
    <?= $text ?>
</div>
