<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define("BX_CRONTAB", true);
define('BX_WITH_ON_AFTER_EPILOG', true);
define('BX_NO_ACCELERATOR_RESET', true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);

CEvent::CheckEvents();

if(CModule::IncludeModule('sender'))
{
	\Thurly\Sender\MailingManager::checkPeriod(false);
	\Thurly\Sender\MailingManager::checkSend();
}

require($_SERVER['DOCUMENT_ROOT']."/thurly/modules/main/tools/backup.php");

CMain::FinalActions();
?>
