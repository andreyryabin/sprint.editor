<?php /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?><?php

use Sprint\Editor\Blocks\FlickrPhotoset;

$images = FlickrPhotoset::getPhotos($block['photoset_id']);

?><?php if (!empty($images)) { ?>
    <div class="sp-gallery">
        <div class="sp-gallery-items">
            <?php foreach ($images as $image) { ?>
                <div class="sp-gallery-item">
                    <a data-fancybox="gallery" class="sp-gallery-item-img-wrapper fancy" rel="media-gallery" href="<?= $image['DETAIL_SRC'] ?>">
                        <img alt="<?= $image['DESCRIPTION'] ?>" src="<?= $image['SRC'] ?>">
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
