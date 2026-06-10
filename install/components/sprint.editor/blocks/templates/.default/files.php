<?php /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?><?php
?><?php if (!empty($block['files'])) { ?>
    <ol>
        <?php foreach ($block['files'] as $item) { ?>
            <li><a download="<?= htmlspecialcharsbx($item['file']['ORIGINAL_NAME']) ?>" title="<?= htmlspecialcharsbx($item['desc']) ?>" href="<?= htmlspecialcharsbx($item['file']['SRC']) ?>"><?= htmlspecialcharsbx($item['file']['ORIGINAL_NAME']) ?></a></li>
        <?php } ?>
    </ol>
<?php } ?>
