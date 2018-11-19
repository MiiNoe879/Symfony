<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @var CAllUser $USER
 * @var CAllMain $APPLICATION
 */

if(!IsModuleInstalled("bizcard"))
{
	$APPLICATION->RestartBuffer();
	\Thurly\Main\Web\Json::encode(array(
		"STATUS"=>"failed",
		"ERROR"=>"Module \"bizcard\" is not installed"
	));
}
else
{
	include(\Thurly\Main\Application::getDocumentRoot()."/thurly/tools/bizcard/bizcard.php");
}
