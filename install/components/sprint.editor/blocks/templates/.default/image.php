<?php /** @var $block array */ ?><?php
$image = Sprint\Editor\Blocks\Image::getImage(
    $block, [
        'width'  => 1024,
        'height' => 768,
        'exact'  => 0,
        //'jpg_quality' => 75
    ]
);
?><?php if ($image) { ?>
    <div class="sp-image"><img loading="lazy" alt="<?= $image['DESCRIPTION'] ?>" src="<?= $image['SRC'] ?>" width="<?= $image['WIDTH'] ?>" height="<?= $image['HEIGHT'] ?>"></div>
<?php } ?>
