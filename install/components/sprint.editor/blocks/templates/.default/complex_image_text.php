<?/** @var $block array */?><?

$text = Sprint\Editor\Blocks\Text::getValue($block['text']);
$image = Sprint\Editor\Blocks\Image::getImage($block['image'], array(
    'width' => 320,
    'height' => 240,
    'exact' => 0
));
?>

<div class="c-image-text" style="background: #eee;margin: 10px 0 10px; min-height: 250px;">
    <?if ($image):?>
        <img style="float: left;width: 320px; margin: 0 10px 10px 0;" alt="<?=$image['DESCRIPTION']?>" src="<?=$image['SRC']?>">
    <?endif;?>
    <?=$text?>
</div>
