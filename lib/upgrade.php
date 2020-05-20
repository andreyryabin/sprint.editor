<?php

namespace Sprint\Editor;

use Bitrix\Main\DB\Exception;

abstract class Upgrade
{

    private $outMessages = array();
    private $buttons = array();
    private $descr = 'Unknown Upgrade';


    public function addButton($name, $title){
        $this->buttons[$name] = array(
            'name' => $name,
            'title' => $title
        );
    }

    public function setDescription($descr = ''){
        $this->descr = $descr;
    }

    public function getDescriptionForUpgradeManager(){
        return $this->descr;
    }

    public function getButtonsForUpgradeManager(){
        return $this->buttons;
    }

    public function out($msg, $var1 = null, $var2 = null){
        if (func_num_args() > 1) {
            $params = func_get_args();
            $msg = call_user_func_array('sprintf', $params);
        }

        $this->outMessages[] = array(
            'type' => 'OK',
            'msg' => $msg
        );
    }

    public function throwError($msg, $var1 = null, $var2 = null){
        if (func_num_args() > 1) {
            $params = func_get_args();
            $msg = call_user_func_array('sprintf', $params);
        }

        Throw new \Exception($msg);
    }

    public function getOutMessages(){
        return $this->outMessages;
    }

}
