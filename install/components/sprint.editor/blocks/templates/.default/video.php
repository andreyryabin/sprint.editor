<? /** @var $block array */ ?><?

/*
$preview = Sprint\Editor\Blocks\Image::getImage($block['preview'], array(
    'width' => 1024,
    'height' => 768,
    'exact' => 0,
    //'jpg_quality' => 75
));

<img alt="<?= $preview['DESCRIPTION'] ?>" src="<?= $preview['SRC'] ?>">

*/

?><div class="sp-video">
    <?= Sprint\Editor\Blocks\Video::getHtml($block) ?>
</div>
