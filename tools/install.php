<?php
if (php_sapi_name() != 'cli') {
    die('Can not run in this mode. Bye!');
}

set_time_limit(0);
error_reporting(E_ERROR);

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

if (empty($_SERVER["DOCUMENT_ROOT"])) {
    $_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__ . '/../../../../');
}

$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];


fwrite(STDOUT, 'sprint.editor: install script found' . PHP_EOL);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!\CModule::IncludeModule('sprint.editor')) {

    /** @var sprint_migration $ob */
    if ($ob = \CModule::CreateModuleObject('sprint.editor')){
        $ob->DoInstall();
        fwrite(STDOUT, 'sprint.editor: module found and installed' . PHP_EOL);
    } else {
        fwrite(STDOUT, 'sprint.editor: module not found' . PHP_EOL);
    }

} else {
    fwrite(STDOUT, 'sprint.editor: module found and already installed' . PHP_EOL);
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
