<?php /** @var $block array */ ?><?php
$images = Sprint\Editor\Blocks\MedialibElements::getImages(
    $block, [
    'width'  => 300,
    'height' => 300,
    'exact'  => 0,
], [
        'width'  => 1024,
        'height' => 768,
        'exact'  => 0,
    ]
);
?><?php if (!empty($images)) { ?>
    <div class="sp-gallery">
        <div class="sp-gallery-items">
            <?php foreach ($images as $image) { ?>
                <div class="sp-gallery-item">
                    <a data-fancybox="gallery" class="sp-gallery-item-img-wrapper fancy" rel="media-gallery" href="<?= $image['DETAIL_SRC'] ?>">
                        <img alt="<?= $image['DESCRIPTION'] ?>" src="<?= $image['SRC'] ?>">
                        <?php if (!empty($image['DESCRIPTION'])) { ?>
                            <div class="sp-gallery-item-text">
                                <div class="sp-gallery-item-text-content"><?= $image['DESCRIPTION'] ?></div>
                            </div>
                        <?php } ?>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
