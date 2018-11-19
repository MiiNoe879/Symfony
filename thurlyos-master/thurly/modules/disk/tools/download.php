<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if(!\Thurly\Main\Loader::includeModule('disk'))
{
	die;
}

if(empty($_GET['action']))
{
	die;
}

$controller = new \Thurly\Disk\DownloadController();
$controller
	->setActionName($_GET['action'])
	->exec()
;