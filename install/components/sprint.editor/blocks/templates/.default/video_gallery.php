<? /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?><?

$this->registerCss('/bitrix/admin/sprint.editor/assets/video-gallery/css/sprint-video-gallery.css');
$this->registerJs('/bitrix/admin/sprint.editor/assets/video-gallery/js/jquery.nicescroll.min.js');
$this->registerJs('/bitrix/admin/sprint.editor/assets/video-gallery/js/sprint-video-gallery.js');
$this->registerJs('/bitrix/admin/sprint.editor/assets/video-gallery/js/init.js');

$items = Sprint\Editor\Blocks\VideoGallery::getItems(
    $block, [
    'width'  => 320,
    'height' => 240,
    'exact'  => 0,
], [
    'width'  => 1024,
    'height' => 768,
    'exact'  => 0,
]
);

?><? if (!empty($items)): ?>
    <div class="sp-video-gallery">
        <? foreach ($items as $item): ?>
            <? if (!empty($item['YOUTUBE_CODE'])): ?>
                <div data-type="youtube" data-src="<?= $item['YOUTUBE_CODE'] ?>"><img src="<?= $item['SRC'] ?>" alt="<?= $item['DESCRIPTION'] ?>"></div>
            <? else: ?>
                <div data-type="image" data-src="<?= $item['DETAIL_SRC'] ?>"><img src="<?= $item['SRC'] ?>" alt="<?= $item['DESCRIPTION'] ?>"></div>
            <? endif ?>
        <? endforeach; ?>
    </div>
<? endif; ?>
