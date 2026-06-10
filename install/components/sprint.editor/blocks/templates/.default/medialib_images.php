<?php /** @var $block array */ ?><?php

$images = Sprint\Editor\Blocks\MedialibElements::getImages(
        $block,
        [
                'width'  => 300,
                'height' => 300,
                'exact'  => 0,
        ],
        [
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
                    <a data-fancybox="gallery" class="sp-gallery-item-img-wrapper fancy" rel="media-gallery" href="<?= htmlspecialcharsbx($image['DETAIL_SRC']) ?>">
                        <img alt="<?= htmlspecialcharsbx($image['DESCRIPTION']) ?>" src="<?= htmlspecialcharsbx($image['SRC']) ?>">
                        <?php if (!empty($image['DESCRIPTION'])) { ?>
                            <div class="sp-gallery-item-text">
                                <div class="sp-gallery-item-text-content"><?= htmlspecialcharsbx($image['DESCRIPTION']) ?></div>
                            </div>
                        <?php } ?>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>