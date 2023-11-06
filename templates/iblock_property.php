<?php
 /**
  * @var $inputName
  * @var $settings
  * @var $userfiles
  *
  */
?>
<?php foreach ($settings as $code => $val):?>
<tr>
    <td><?=GetMessage('SPRINT_EDITOR_SETTINGS_' . $code)?>:</td>
    <td>
        <?php if ($code == 'SETTINGS_NAME'):?>
        <select style="width: 250px" name="<?= $inputName?>[<?=$code?>]">
            <?php foreach ($userfiles as $userFileValue => $userFileTitle):?>
            <option <?php if($val == $userFileValue) echo 'selected="selected"';?> value="<?=$userFileValue?>"><?=$userFileTitle?></option>
            <?php endforeach;?>
        </select>
        <?php else:?>
        <input value="Y" type="checkbox" name="<?= $inputName?>[<?=$code?>]" <?php if($val == 'Y') echo 'checked="checked"';?>/>
        <?php endif?>
    </td>
</tr>

<?php endforeach;?>
