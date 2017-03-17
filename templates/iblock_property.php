<?php
 /**
  * @var $inputName
  * @var $settings
  */
?>
<?foreach ($settings as $code => $val):?>
<tr>
    <td><?=GetMessage('SPRINT_EDITOR_SETTINGS_' . $code)?>:</td>
    <td>
        <input value="Y" type="checkbox" name="<?= $inputName?>[<?=$code?>]" <? if($val == 'Y') echo 'checked="checked"';?>/>
    </td>
</tr>
<?endforeach;?>