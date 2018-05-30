<?php

namespace Sprint\Editor;

class UpgradeManager
{


    protected static $executeMessages = array();

    public static function outMessages($name){
        if (isset(self::$executeMessages[$name])){
            foreach (self::$executeMessages[$name] as $msg){
                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                \CAdminMessage::ShowMessage(array(
                    "MESSAGE" => Locale::convertToWin1251IfNeed($msg['msg']),
                    'HTML' => true,
                    'TYPE' => $msg['type']
                ));
            }
        }
    }

    public static function executeTaskFromRequest($request){
        $buttons = $obj->getButtonsForUpgradeManager();
        foreach ($buttons as $buttonName => $button){
            if (isset($request[$buttonName]) && method_exists($obj,$buttonName)){
                $ok = $obj->$buttonName();
            }
        }


    }

    public static function executeUpgrade($name, $action = 'execute'){
        $ok = self::doExecute($name,$action);
        self::markClass($name, $ok, 'upgrade');
        return $ok;
    }

    public static function executeTask($name, $action = 'execute'){
        $ok = self::doExecute($name,$action);
        return $ok;
    }
    
    public static function getTasks(){
        return self::findClasses('task');
    }

    protected static function initClass($name){
        $file = Module::getModuleDir() . '/upgrades/' . $name . '.php';

        if (!is_file($file)){
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        require_once($file);

        $class = 'Sprint\Editor\\' . $name;

        if (!class_exists($class)) {
            return false;
        }


        /** @var Upgrade $obj */
        $obj = new $class();

        return $obj;
    }

    protected static function findClasses($type){
        $installed = Module::getDbOption('installed_' . $type, '');
        $installed = explode('|', $installed);
        
        $directory = new \DirectoryIterator(Module::getModuleDir() . '/upgrades/');
        $result = array();
        /* @var $item \SplFileInfo */
        foreach ($directory as $item) {
            $fileName = pathinfo($item->getPathname(), PATHINFO_FILENAME);
            
            if (!self::checkClassName($fileName, $type)) {
                continue;
            }
            $obj = self::initClass($fileName);
            if (!$obj) {
                continue;
            }

            $descr = $obj->getDescriptionForUpgradeManager();
            $descr =  Locale::convertToWin1251IfNeed($descr);
            $isinst = in_array($fileName, $installed) ? 'yes' : 'no';

            $result[] = array(
                'name' => $fileName,
                'installed' => $isinst,
                'description' => $descr,
                'buttons' => $obj->getButtonsForUpgradeManager()
            );
        }

        usort($result, function($a, $b){
            return strnatcmp($a["name"], $b["name"]);
        });
        
        return $result;
    }

    protected static function doExecute($name, $action = 'execute'){
        self::$executeMessages[$name] = array();
        $obj = self::initClass($name);
        if (!$obj){
            self::$executeMessages[$name] = array('type' => 'ERROR', 'msg' => 'Class not found');
            return false;
        }
        try {

            $ok = false;

            if (method_exists($obj, $action)){
                $ok = $obj->$action();
            }

            $messages = $obj->getOutMessages();
            foreach ($messages as $msg) {
                self::$executeMessages[$name][] = $msg;
            }
            if ($ok === false) {
                self::$executeMessages[$name][] = array('type' => 'ERROR', 'msg' => 'Error');
                return false;
            }
        } catch (\Exception $e){
            self::$executeMessages[$name][] = array('type' => 'ERROR', 'msg' => 'Exception: ' . $e->getMessage());
            return false;
        }
        return true;
    }
    
    protected static function markClass($name, $asInstalled = true, $type){
        $installed = Module::getDbOption('installed_' . $type, '');
        $installed = explode('|', $installed);

        if ($asInstalled){
            if (!in_array($name, $installed)){
                $installed[] = $name;
            }
        } else {
            $key = array_search($name, $installed);
            if ($key >= 0){
                unset($installed[$key]);
            }
        }
        Module::setDbOption('installed_' . $type, implode('|', $installed));
    }
    
    protected static function checkClassName($fileName, $type){
        return preg_match('/^'.ucfirst($type).'\d+$/i', $fileName);
    }

}
