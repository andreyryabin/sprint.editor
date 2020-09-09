<? /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?>

<? if (!empty($block['files'])): ?>
    <ul class="big-list my_files m0">
        <? foreach ($block['files'] as $item): ?>
            <li>
                <a target="_blank" title="<?= $item['desc'] ?>" href="<?= $item['file']['SRC'] ?>">
                    <i class="fa fa-file-text-o mr10" aria-hidden="true"></i>
                    <?=($item[desc])?$item[desc]:$item['file']['ORIGINAL_NAME']?>
                </a>
            </li>
        <? endforeach; ?>
    </ul>
<? endif; ?>
