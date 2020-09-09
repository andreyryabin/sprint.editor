<?
$images = Sprint\Editor\Blocks\Gallery::getImages(
    $block, [
    'width'  => 200,
    'height' => 200,
    'exact'  => 0,
], [
    'width'  => 1024,
    'height' => 768,
    'exact'  => 0,
]
);
?>

<? if (!empty($images)): ?>
    <div class="sp-gallery" style="display:none">
        <div class="sp-gallery-items">
            <? foreach ($images as $image): ?>
                <div class="sp-gallery-item">
                    <a style="display: block;height: 100%;" data-fancybox="gallery" class="sp-gallery-item-img-wrapper fancy" rel="media-gallery" href="<?= $image['DETAIL_SRC'] ?>">
                        <img alt="<?= $image['DESCRIPTION'] ?>" src="<?= $image['SRC'] ?>" class="no-lazy">
                        <? if (!empty($image['DESCRIPTION'])): ?>
                            <div class="sp-gallery-item-text">
                                <div class="sp-gallery-item-text-content"><?= $image['DESCRIPTION'] ?></div>
                            </div>
                        <? endif; ?>
                    </a>
                </div>
            <? endforeach; ?>
        </div>
    </div>
<? endif; ?>



<script>
    document.addEventListener("DOMContentLoaded", ()=>{
        $('.sp-gallery').show();
        $(".sp-gallery-items").justifiedGallery();
    });
</script>