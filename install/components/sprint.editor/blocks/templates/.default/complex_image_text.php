<?/** @var $block array */?><?

$text = Sprint\Editor\Blocks\Text::getValue($block['text']);
$image = Sprint\Editor\Blocks\Image::getImage($block['image'], array(
    'width' => 320,
    'height' => 240,
    'exact' => 0
));
?>

<div class="sp-image-text">
    <?if ($image):?>
        <img alt="<?=$image['DESCRIPTION']?>" src="<?=$image['SRC']?>">
    <?endif;?>
    <?=$text?>
</div>
