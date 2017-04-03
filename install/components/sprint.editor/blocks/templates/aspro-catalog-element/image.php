<? /** @var $block array */ ?><?
$image = Sprint\Editor\Blocks\Image::getImage($block, array(
    'width' => 825,
    'height' => 600,
    'exact' => 0
));
?><? if ($image): ?>
    <div style="max-width:825px;">
        <img alt="<?= $image['DESCRIPTION'] ?>" src="<?= $image['SRC'] ?>" style="width:100%;" title="<?= $image['DESCRIPTION'] ?>">
    </div>
<? endif; ?>