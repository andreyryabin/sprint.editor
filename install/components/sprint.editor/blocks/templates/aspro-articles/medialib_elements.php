<? /** @var $block array */ ?><?
$images = Sprint\Editor\Blocks\MedialibElements::getImages($block, array(
    'width' => 800,
    'height' => 600,
    'exact' => 0
));
?>
<div class="row galley">
    <ul class="module-gallery-list">
        <? foreach ($images as $image): ?>
            <li class="item_block">
                <a href="<?= $image['ORIGIN_SRC'] ?>" class="fancy" data-fancybox-group="gallery">
                    <img src="<?= $image['SRC'] ?>" alt="<?= $image['DESCRIPTION'] ?>" title="<?= $image['DESCRIPTION'] ?>">
                </a>
            </li>
        <? endforeach; ?>
    </ul>
    <div class="clear"></div>
</div>