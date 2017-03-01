<? /** @var $block array */

$image = Sprint\Editor\Blocks\Image::getImage($block['image'], array(
    'width' => 50,
    'height' => 50,
    'exact' => 0
));
?>
<div itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
    Publisher: <span itemprop="name"><?= $block['publisher']['value'] ?></span>
    <?if ($image):?>
    <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
        <img itemprop="image url" src="<?= $image['SRC'] ?>" alt="logo"/>
    </div>
    <?endif;?>
</div>