<?
function createDox($item){?>
        <? foreach ($item as $item): ?>
            <li>
                <a download="<?= $item['file']['ORIGINAL_NAME'] ?>" title="<?= $item['desc'] ?>" href="<?= $item['file']['SRC'] ?>">
                <?= $item['file']['ORIGINAL_NAME'] ?>
            </a>
            </li>
                <?if(count($item['children'])):?>
                    <ol>
                        <?=createDox($item['children'])?>
                    </ol>
                <?endif;?>
        <? endforeach; ?>
<?}?>
<? if (!empty($block['files'])): ?>
    <ol>
        <?=createDox($block['files'])?>
    </ol>
<? endif; ?>
