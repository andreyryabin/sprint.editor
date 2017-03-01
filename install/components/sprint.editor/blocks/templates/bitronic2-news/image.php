<?/** @var $block array */?><?
$image = Sprint\Editor\Blocks\Image::getImage($block, array(
    'width' => 350,
    'height' => 300,
    'exact' => 0
));
?><?if ($image):?>
<div class="row images-row">
	<div class="img-container">
		<img src="<?=$image['SRC']?>" alt="<?=$image['DESCRIPTION']?>" title="<?=$image['DESCRIPTION']?>">
	</div>
</div>
<?endif;?>
