<?php
/** @global $APPLICATION CMain */

use Sprint\Editor\Exceptions\ComplexBuilderException;

global $APPLICATION;
$APPLICATION->SetTitle(GetMessage('SPRINT_EDITOR_COMPLEX_BUILDER'));

$request = Bitrix\Main\Context::getCurrent()->getRequest();
Sprint\Editor\ComplexBuilder::init();

$currentBlockName = (string)$request->get('currentBlockName');

$currentEditorValue = Sprint\Editor\ComplexBuilder::getBuildJson($currentBlockName);
$currentBlockName = $currentEditorValue ? $currentBlockName : '';

$lastErr = '';

if ($request->isPost() && check_bitrix_sessid()) {
    $currentEditorValue = $request->getPost('block_template');

    if ($request->getPost('save_block')) {
        try {
            if ($currentBlockName) {
                Sprint\Editor\ComplexBuilder::updateBlock(
                    $currentBlockName,
                    $currentEditorValue,
                );
            } else {
                $currentBlockName = Sprint\Editor\ComplexBuilder::createBlock(
                    $request->getPost('block_name'),
                    $currentEditorValue
                );
            }

            LocalRedirect(
                'sprint_editor.php?' . http_build_query([
                    'lang'             => LANGUAGE_ID,
                    'currentBlockName' => $currentBlockName,
                    'showpage'         => $request->get('showpage'),
                ])
            );
        } catch (ComplexBuilderException $e) {
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
        Sprint\Editor\ComplexBuilder::deleteBlock($currentBlockName);
        LocalRedirect(
            'sprint_editor.php?' . http_build_query([
                'lang'     => LANGUAGE_ID,
                'showpage' => $request->get('showpage'),
            ])
        );
    }
}

$complexBlocks = Sprint\Editor\ComplexBuilder::getComplexBlocks();
$blocksToolbar = Sprint\Editor\ComplexBuilder::getBlocksToolbar();

$currentEditorParams = CUtil::PhpToJSObject([
    "uniqid"  => "complex_builder",
    "toolbar" => $blocksToolbar,
]);

$currentEditorValue = CUtil::PhpToJSObject(
    json_decode($currentEditorValue, true)
);

?>

<div class="adm-detail-content" style="padding: 0">
    <div class="adm-detail-content-item-block">
        <table class="adm-detail-content-table edit-table">
            <tbody>
            <tr class="">
                <td class="adm-detail-valign-top" style="width: 40%">
                    <div class="sp-side-left">
                        <a class="sp-link sp-link-new <?= ($currentBlockName == '' ? 'sp-link-active' : '') ?>"
                           href="<?= 'sprint_editor.php?' . http_build_query(
                               [
                                   'lang'             => LANGUAGE_ID,
                                   'currentBlockName' => '',
                                   'showpage'         => $request->get('showpage'),
                               ]
                           ) ?>">Новый блок</a>
                    </div>
                    <div class="sp-side-left">
                        <?php foreach ($complexBlocks as $complexBlock) { ?>
                            <a class="sp-link <?= ($currentBlockName == $complexBlock['name'] ? 'sp-link-active' : '') ?>"
                               href="<?= 'sprint_editor.php?' . http_build_query(
                                   [
                                       'lang'             => LANGUAGE_ID,
                                       'currentBlockName' => $complexBlock['name'],
                                       'showpage'         => $request->get('showpage'),
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
                            <div class="sp-x-field"><strong>Название блока</strong></div>
                            <div class="sp-x-field">
                                <?php if ($currentBlockName) { ?>
                                    <?= $currentBlockName ?>
                                <?php } else { ?>
                                    complex_
                                    <input name="block_name" placeholder="block_123" type="text" value="<?= $request->getPost('block_name') ?>">
                                <?php } ?>
                            </div>
                            <div class="sp-x-field">
                                <div class="sp-table">
                                    <div class="sp-row">
                                        <div class="sp-col">
                                            <strong>Заголовок</strong>
                                        </div>
                                        <div class="sp-col">
                                            <strong>Сортировка</strong>
                                        </div>
                                    </div>
                                    <div class="sp-row">
                                        <div class="sp-col">
                                            <input name="block_title" placeholder="Составной блок" type="text" value="">
                                        </div>
                                        <div class="sp-col">
                                            <input name="block_sort" placeholder="Сортировка" type="text" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="complex_builder_editor"></div>
                        <textarea name="block_template" id="complex_builder_result" style="display: none;"></textarea>
                        <div style="background-color: #e3ecee;border: 1px solid #c4ced2;padding: 10px;margin-bottom: 10px">
                            <input class="adm-btn" name="save_block" value="<?= GetMessage('SPRINT_EDITOR_pack_save') ?>" type="submit">
                            <?php if ($currentBlockName) { ?>
                                <input class="adm-btn" name="delete_block" value="<?= GetMessage('SPRINT_EDITOR_pack_delete') ?>" type="submit">
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
        complex_builder($, <?=$currentEditorParams?>, <?=$currentEditorValue?>);
    });
</script>
