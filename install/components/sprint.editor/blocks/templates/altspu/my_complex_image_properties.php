<?
$image = Sprint\Editor\Blocks\Image::getImage(
    $block['image'], [
    'width'  => 320,
    'exact'  => 0,
]
);
?>
<div class="mb10 ov-h">
    <img src="<?=$image['SRC']?>" alt="" class="col col-ld-4 col-dt-5 col-12 col-mb-12">
    <table class="col col-ld-8 col-dt-7 col-12 col-mb-12 ">
        <? foreach ($block['properties']['elements'] as $item): ?>
            <tr>
                <td class="sp-properties_title"><?= $item['title'] ?></td>
                <td class="sp-properties_text"><?= $item['text'] ?></td>
            </tr>
        <? endforeach; ?>
    </table>
</div>

