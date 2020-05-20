<?php

namespace Sprint\Editor;

use Exception;

abstract class Upgrade
{
    private $outMessages = [];
    private $buttons     = [];
    private $descr       = 'Unknown Upgrade';

    public function addButton($name, $title)
    {
        $this->buttons[$name] = [
            'name'  => $name,
            'title' => $title,
        ];
    }

    public function setDescription($descr = '')
    {
        $this->descr = $descr;
    }

    public function getDescriptionForUpgradeManager()
    {
        return $this->descr;
    }

    public function getButtonsForUpgradeManager()
    {
        return $this->buttons;
    }

    public function out($msg, $var1 = null, $var2 = null)
    {
        if (func_num_args() > 1) {
            $params = func_get_args();
            $msg = call_user_func_array('sprintf', $params);
        }

        $this->outMessages[] = [
            'type' => 'OK',
            'msg'  => $msg,
        ];
    }

    public function throwError($msg, $var1 = null, $var2 = null)
    {
        if (func_num_args() > 1) {
            $params = func_get_args();
            $msg = call_user_func_array('sprintf', $params);
        }

        throw new Exception($msg);
    }

    public function getOutMessages()
    {
        return $this->outMessages;
    }
}
