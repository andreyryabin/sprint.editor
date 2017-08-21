<?/** @var $block array */?><?
$images = Sprint\Editor\Blocks\MedialibElements::getImages($block, array(
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
    <ul class="sp-gallery-items">
        <?foreach ($images as $image):?>
            <li class="sp-gallery-item">
                <a data-fancybox="gallery" class="sp-gallery-item-img-wrapper fancy" rel="media-gallery" href="<?=$image['DETAIL_SRC']?>">
                    <img alt="<?=$image['DESCRIPTION']?>" src="<?=$image['SRC']?>">
                    <div class="sp-gallery-item-text">
                        <div class="sp-gallery-item-text-content"><?=$image['DESCRIPTION']?></div>
                    </div>
                </a>
            </li>
        <?endforeach;?>
    </ul>
</div>
<?endif;?>