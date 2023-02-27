<?php /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */ ?><?php
?><?php if (!empty($block['files'])) { ?>
    <ol>
        <?php foreach ($block['files'] as $item) { ?>
            <li><a download="<?= $item['file']['ORIGINAL_NAME'] ?>" title="<?= $item['desc'] ?>" href="<?= $item['file']['SRC'] ?>"><?= $item['file']['ORIGINAL_NAME'] ?></a></li>
        <?php } ?>
    </ol>
<?php } ?>
