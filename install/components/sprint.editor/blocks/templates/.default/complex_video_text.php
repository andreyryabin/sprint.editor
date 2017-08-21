<?/** @var $block array */?><?

/*
$preview = Sprint\Editor\Blocks\Image::getImage($block['preview'], array(
    'width' => 1024,
    'height' => 768,
    'exact' => 0,
    //'jpg_quality' => 75
));
*/

$text = Sprint\Editor\Blocks\Text::getValue($block['text']);

$video = Sprint\Editor\Blocks\Video::getHtml($block['video'], array(
    'width' => 640,
    'height' => 480
));

?>
<div class="sp-video-text">
    <div><?=$video?></div>
    <div><?=$text?></div>
</div>
