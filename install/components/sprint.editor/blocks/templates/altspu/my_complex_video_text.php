<? /** @var $block array */ ?><?

/*
$preview = Sprint\Editor\Blocks\Image::getImage($block['preview'], array(
    'width' => 1024,
    'height' => 768,
    'exact' => 0,
    //'jpg_quality' => 75
));
*/

$text = Sprint\Editor\Blocks\Text::getValue($block['text']);

$video = Sprint\Editor\Blocks\Video::getHtml(
    $block['video']
);

?>
<div>
    <div class="ta-center video"><?= $video ?></div>
    <div><?= $text ?></div>
</div>
