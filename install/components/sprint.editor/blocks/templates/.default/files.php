<? /**
 * @var $block array
 * @var $this \SprintEditorBlocksComponent
 */ ?><?
?><? if (!empty($block['files'])): ?>
    <ol>
        <? foreach ($block['files'] as $item): ?>
            <li><a download="<?= $item['file']['ORIGINAL_NAME'] ?>" title="<?= $item['desc'] ?>" href="<?= $item['file']['SRC'] ?>"><?= $item['file']['ORIGINAL_NAME'] ?></a></li>
        <? endforeach; ?>
    </ol>
<? endif; ?>