<?/**
 * @var $block array
 * @var $this \SprintEditorBlocksComponent
 */?><?
$images = Sprint\Editor\Blocks\Gallery::getImages($block, array(
    'width' => 300,
    'height' => 300,
    'exact' => 0,
), array(
    'width' => 1024,
    'height' => 768,
    'exact' => 0,
));
?><?if (!empty($images)):?>
<div class="sp-gallery">
    <div class="sp-gallery-items">
        <?foreach ($images as $image):?>
        <div class="sp-gallery-item">
            <a data-fancybox="gallery" class="sp-gallery-item-img-wrapper fancy" rel="media-gallery" href="<?=$image['DETAIL_SRC']?>">
                <img alt="<?=$image['DESCRIPTION']?>" src="<?=$image['SRC']?>">
                <?if (!empty($image['DESCRIPTION'])):?>
                <div class="sp-gallery-item-text">
                    <div class="sp-gallery-item-text-content"><?=$image['DESCRIPTION']?></div>
                </div>
                <?endif;?>
            </a>
        </div>
        <?endforeach;?>
    </div>
</div>
<?endif;?>