<?php
require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/thurly/modules/main/include/prolog_before.php");

/** @var CAllMain $APPLICATION */
$diskEnabled = false;
if(IsModuleInstalled('disk'))
{
	$diskEnabled =
		\Thurly\Main\Config\Option::get('disk', 'successfully_converted', false) &&
		CModule::includeModule('disk');
	if($diskEnabled && \Thurly\Disk\Configuration::REVISION_API >= 5)
	{
		$storageController = new Thurly\Disk\ThurlyOSDisk\Legacy\StorageController();
		$storageController
			->setActionName($_REQUEST['action'])
			->exec();
	}
	else
	{
		$diskEnabled = false;
	}
}
if(!$diskEnabled)
{
	$APPLICATION->IncludeComponent('thurly:webdav.disk', '', array('VISUAL' => false));
	CMain::FinalActions();
	die();
}