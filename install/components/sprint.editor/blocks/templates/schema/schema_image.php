<?/** @var $block array */

$image = Sprint\Editor\Blocks\Image::getImage($block['image'], array(
    'width' => 400,
    'height' => 300,
    'exact' => 0
));

?><?if ($image):?>
<div itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
    <img alt="<?=$image['DESCRIPTION']?>" itemprop="image url" src="<?=$image['SRC']?>"/>
    <meta itemprop="width" content="<?=$image['WIDTH']?>">
    <meta itemprop="height" content="<?=$image['HEIGHT']?>">
</div>
<?endif;?>