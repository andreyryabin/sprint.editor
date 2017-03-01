<? /** @var $block array */ ?><?$pdate = date('Y-m-d', strtotime($block['pubdate']['value']))?>
<div>
    Date published: <span itemprop="datePublished" content="<?= $pdate ?>"><?= $pdate ?></span>
</div>