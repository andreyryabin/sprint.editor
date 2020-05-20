<? /** @var $block array */ ?><?
$image = Sprint\Editor\Blocks\Image::getImage(
    $block, [
    'width'  => 1024,
    'height' => 768,
    'exact'  => 0,
    //'jpg_quality' => 75
]
);
?><? if ($image): ?>
    <div class="sp-image"><img alt="<?= $image['DESCRIPTION'] ?>" src="<?= $image['SRC'] ?>"></div>
<? endif; ?>
