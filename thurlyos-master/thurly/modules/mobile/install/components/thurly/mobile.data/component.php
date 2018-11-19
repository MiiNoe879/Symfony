<? use Thurly\Main\Context;
use Thurly\Mobile\Auth;

if (!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION CAllMain
 * @var $USER CAllUser
 * @var $USER CAllUser
 * @var $arParams array
 */
Thurly\Main\Loader::includeModule("mobileapp");
Thurly\Main\Loader::includeModule("mobile");
Thurly\MobileApp\Mobile::getInstance();

include_once(dirname(__FILE__) . "/functions.php");

defineApiVersion();

$isSessidValid = true;
if(array_key_exists("sessid", $_REQUEST) && strlen($_REQUEST["sessid"]) > 0)
{
	$isSessidValid = check_thurly_sessid();
}

if ($USER->IsAuthorized() && $isSessidValid)
{
	$isBackground = Context::getCurrent()->getServer()->get("HTTP_BX_MOBILE_BACKGROUND");
	if($isBackground != "true" && ($_REQUEST["mobile_action"] && $_REQUEST["mobile_action"] != "checkout"))
    {
        Thurly\Mobile\User::setOnline();
    }
}

if ($_REQUEST["mobile_action"])//Executing some action
{
	$APPLICATION->RestartBuffer();
	$action = $_REQUEST["mobile_action"];
	$actionList = new Thurly\Mobile\Action();
	$actionList->executeAction($action, $arParams);

	CMain::FinalActions();
	die();
}
elseif ($_REQUEST["captcha_sid"])//getting captcha image
{
	$APPLICATION->RestartBuffer();
	$actionList = new Thurly\Mobile\Action();
	$actionList->executeAction("get_captcha", $arParams);
	die();
}
elseif ($_REQUEST["manifest_id"])//getting content of appcache manifest
{
	include($_SERVER["DOCUMENT_ROOT"] .\Thurly\Main\Data\AppCacheManifest::MANIFEST_CHECK_FILE);
	die();
}
elseif(!$USER->IsAuthorized() || !$isSessidValid)
{
	$APPLICATION->RestartBuffer();
	Auth::setNotAuthorizedHeaders();
	echo json_encode(Auth::getNotAuthorizedResponse());
	die();
}

$this->IncludeComponentTemplate();
?>
