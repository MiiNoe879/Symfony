<?
define("IM_AJAX_INIT", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("imopenlines"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'IMOL_MODULE_NOT_INSTALLED'));
	CMain::FinalActions();
	die();
}

if (!check_thurly_sessid())
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
	CMain::FinalActions();
	die();
}

$permissions = \Thurly\ImOpenlines\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\ImOpenlines\Security\Permissions::ENTITY_SETTINGS, \Thurly\ImOpenlines\Security\Permissions::ACTION_MODIFY))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'AUTHORIZE_ERROR'));
	CMain::FinalActions();
	die();
}

if(!\Thurly\ImOpenlines\Security\Helper::canUse())
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'LICENSE_ERROR'));
	CMain::FinalActions();
	die();
}

if ($_POST['action'] == 'deleteRole')
{
	$arSend['ERROR'] = '';
	$roleId = (int)$_POST['roleId'];

	if($roleId > 0)
	{
		\Thurly\ImOpenlines\Security\RoleManager::deleteRole($roleId);
	}

	echo CUtil::PhpToJsObject($arSend);
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'UNKNOWN_ERROR'));
}

CMain::FinalActions();
die();