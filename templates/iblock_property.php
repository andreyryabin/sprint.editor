<?php
 /**
  * @var $inputName
  * @var $settings
  * @var $userfiles
  *
  */
?>
<?foreach ($settings as $code => $val):?>
<tr>
    <td><?=GetMessage('SPRINT_EDITOR_SETTINGS_' . $code)?>:</td>
    <td>
    <?if ($code == 'SETTINGS_NAME'):?>
        <select style="width: 250px" name="<?= $inputName?>[<?=$code?>]">
        <?foreach ($userfiles as $userFileValue => $userFileTitle):?>
            <option <? if($val == $userFileValue) echo 'selected="selected"';?> value="<?=$userFileValue?>"><?=$userFileTitle?></option>
        <?endforeach;?>
        </select>
    <?else:?>
        <input value="Y" type="checkbox" name="<?= $inputName?>[<?=$code?>]" <? if($val == 'Y') echo 'checked="checked"';?>/>
    <?endif?>
    </td>
</tr>

<?endforeach;?>