<?php
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/thurly/modules/main/include/prolog_before.php");

if (Thurly\Main\Loader::includeModule('dav'))
{

	$handler = new \Thurly\Dav\Profile\RequestHandler();
	$handler->process();
}
