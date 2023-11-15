<?php
/** @global $APPLICATION CMain */

use Sprint\Editor\ComplexBuilder;
use Sprint\Editor\Exceptions\AdminPageException;

global $APPLICATION;
$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_COMPLEX_BUILDER'));

$request = Bitrix\Main\Context::getCurrent()->getRequest();
ComplexBuilder::init();

$currentBlockId = (string)$request->get('currentBlockId');
$currentBuildJson = ComplexBuilder::getBuildJson($currentBlockId);
$currentBlockId = $currentBuildJson ? $currentBlockId : '';

$newBlockId = '';
$lastErr = '';

if ($request->isPost() && check_bitrix_sessid()) {
    if ($request->getPost('save_block')) {
        try {
            $currentBuildJson = $request->getPost('block_content');

            if ($currentBlockId) {
                ComplexBuilder::updateBlock(
                    $currentBlockId,
                    $currentBuildJson,
                );
            } else {
                $newBlockId = $request->getPost('block_id');
                $currentBlockId = ComplexBuilder::createBlock(
                    $newBlockId,
                    $currentBuildJson
                );
            }

            LocalRedirect(
                'sprint_editor.php?' . http_build_query([
                    'lang'           => LANGUAGE_ID,
                    'currentBlockId' => $currentBlockId,
                    'showpage'       => $request->get('showpage'),
                ])
            );
        } catch (AdminPageException $e) {
            $lastErr = (new CAdminMessage(
                [
                    "MESSAGE" => $e->getMessage(),
                    'HTML'    => true,
                    'TYPE'    => 'ERROR',
                ]
            ))->Show();
        }
    }
    if ($request->getPost('delete_block')) {
        ComplexBuilder::deleteBlock($currentBlockId);
        LocalRedirect(
            'sprint_editor.php?' . http_build_query([
                'lang'     => LANGUAGE_ID,
                'showpage' => $request->get('showpage'),
            ])
        );
    }
}

$complexBlocks = ComplexBuilder::getComplexBlocks();
$blocksToolbar = ComplexBuilder::getBlocksToolbar();

$currentEditorParams = CUtil::PhpToJSObject([
    "uniqid"  => "complex_builder",
    "toolbar" => $blocksToolbar,
]);

$currentBuildJson = CUtil::PhpToJSObject(
    json_decode($currentBuildJson, true)
);

?>

<div class="adm-detail-content" style="padding: 0">
    <div class="adm-detail-content-item-block">
        <table class="adm-detail-content-table edit-table">
            <tbody>
            <tr class="">
                <td class="adm-detail-valign-top" style="width: 40%">
                    <div class="sp-side-left">
                        <a class="sp-link sp-link-new <?= ($currentBlockId == '' ? 'sp-link-active' : '') ?>"
                           href="<?= 'sprint_editor.php?' . http_build_query(
                               [
                                   'lang'           => LANGUAGE_ID,
                                   'currentBlockId' => '',
                                   'showpage'       => $request->get('showpage'),
                               ]
                           ) ?>"><?= GetMessage('SPRINT_EDITOR_new_block') ?></a>
                    </div>
                    <div class="sp-side-left">
                        <?php foreach ($complexBlocks as $complexBlock) { ?>
                            <a class="sp-link <?= ($currentBlockId == $complexBlock['name'] ? 'sp-link-active' : '') ?>"
                               href="<?= 'sprint_editor.php?' . http_build_query(
                                   [
                                       'lang'           => LANGUAGE_ID,
                                       'currentBlockId' => $complexBlock['name'],
                                       'showpage'       => $request->get('showpage'),
                                   ]
                               ) ?>"><?= $complexBlock['title'] ?></a>
                        <?php } ?>
                    </div>
                </td>
                <td class="adm-detail-valign-top" style="width: 60%">
                    <?= $lastErr ?>
                    <form action="" method="post">
                        <?= bitrix_sessid_post() ?>
                        <div class="sp-x-header">
                            <div class="sp-table sp-table-spacing">
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <strong><?= GetMessage('SPRINT_EDITOR_block_id') ?></strong>
                                    </div>
                                </div>
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <?php if ($currentBlockId) { ?>
                                            <?= $currentBlockId ?>
                                        <?php } else { ?>
                                            complex_
                                            <input name="block_id" placeholder="block_123" type="text" value="<?= $newBlockId ?>">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="sp-table sp-table-spacing">
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <strong><?= GetMessage('SPRINT_EDITOR_block_title') ?></strong>
                                    </div>
                                    <div class="sp-col">
                                        <strong><?= GetMessage('SPRINT_EDITOR_block_sort') ?></strong>
                                    </div>
                                </div>
                                <div class="sp-row">
                                    <div class="sp-col">
                                        <input name="block_title" placeholder="<?= GetMessage('SPRINT_EDITOR_complex_block') ?>" type="text" value="">
                                    </div>
                                    <div class="sp-col">
                                        <input name="block_sort" placeholder="500" type="text" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="complex_builder_editor"></div>
                        <textarea name="block_content" id="complex_builder_result" style="display: none;"></textarea>
                        <div style="background-color: #e3ecee;border: 1px solid #c4ced2;padding: 10px;margin-bottom: 10px">
                            <input class="adm-btn adm-btn-save" name="save_block" value="<?= GetMessage('SPRINT_EDITOR_block_save') ?>" type="submit">
                            <?php if ($currentBlockId) { ?>
                                <input class="adm-btn" name="delete_block" value="<?= GetMessage('SPRINT_EDITOR_block_delete') ?>" type="submit">
                            <?php } ?>
                        </div>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        complex_builder($, <?=$currentEditorParams?>, <?=$currentBuildJson?>);
    });
</script>
