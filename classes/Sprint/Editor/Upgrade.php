<?php

namespace Sprint\Editor;

abstract class Upgrade
{
    abstract public function getDescription();
    abstract public function execute();

    private $outMessages = array();

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

    public function outError($msg, $var1 = null, $var2 = null){
        if (func_num_args() > 1) {
            $params = func_get_args();
            $msg = call_user_func_array('sprintf', $params);
        }

        $this->outMessages[] = array(
            'type' => 'ERROR',
            'msg' => $msg
        );
    }

    public function getOutMessages(){
        return $this->outMessages;
    }

}
