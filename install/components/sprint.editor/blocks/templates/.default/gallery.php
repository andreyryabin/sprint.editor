<?/** @var $block array */?><?
$images = Sprint\Editor\Blocks\Gallery::getImages($block, array(
    'width' => 200,
    'height' => 200,
    'exact' => 0
));
?><div class="c-gallery">
<?foreach ($images as $image):?>
    <a class="fancy" rel="media-gallery" href="<?=$image['ORIGIN_SRC']?>"><img alt="<?=$image['DESCRIPTION']?>" src="<?=$image['SRC']?>"></a>
<?endforeach;?>
</div>