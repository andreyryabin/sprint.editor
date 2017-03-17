<?php
 /**
  * @var $inputName
  * @var $settings
  * @var $defaultEditor
  */
?>
<?foreach ($settings as $code => $val):?>
<tr>
    <td><?=GetMessage('SPRINT_EDITOR_SETTINGS_' . $code)?>:</td>
    <td>
        <?if ($code == 'DEFAULT_VALUE'):?>
            <?=$defaultEditor?>
        <?else:?>
            <input value="Y" type="checkbox" name="<?= $inputName?>[<?=$code?>]" <? if($val == 'Y') echo 'checked="checked"';?>/>
        <?endif?>
    </td>
</tr>
<?endforeach;?>
