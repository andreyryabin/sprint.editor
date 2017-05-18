<?/** @var $block array */?><?
$image = Sprint\Editor\Blocks\Image::getImage($block, array(
    'width' => 1024,
    'height' => 768,
    'exact' => 0,
    //'jpg_quality' => 75
));
?><?if ($image):?>
    <p><img alt="<?=$image['DESCRIPTION']?>" width="100%" src="<?=$image['SRC']?>"></p>
<?endif;?>