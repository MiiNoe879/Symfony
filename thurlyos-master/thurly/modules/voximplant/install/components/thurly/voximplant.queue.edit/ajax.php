<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

use Thurly\Main\Web\Json;

if (!CModule::IncludeModule("voximplant"))
{
	echo Json::encode(array('ERROR' => 'VI_MODULE_NOT_INSTALLED'));
	CMain::FinalActions();
	die();
}

if (!check_thurly_sessid())
{
	echo Json::encode(array('ERROR' => 'SESSION_ERROR'));
	CMain::FinalActions();
	die();
}

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_SETTINGS,\Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
{
	echo Json::encode(array('ERROR' => 'AUTHORIZE_ERROR'));
	CMain::FinalActions();
	die();
}

$action = $_REQUEST['action'];

$sendResult = function(\Thurly\Main\Result $result)
{
	if($result->isSuccess())
	{
		echo \Thurly\Main\Web\Json::encode(array(
			'SUCCESS' => true,
			'DATA' => $result->getData()
		));
	}
	else
	{
		echo \Thurly\Main\Web\Json::encode(array(
			'SUCCESS' => false,
			'ERROR' => implode(';', $result->getErrorMessages())
		));
	}
};

if($action == 'save')
{
	CThurlyComponent::includeComponentClass('thurly:voximplant.queue.edit');
	$request = \Thurly\Main\Text\Encoding::convertEncoding($_POST, 'utf8', SITE_CHARSET);
	$result = CVoximplantQueueEditComponent::save($request);
	$sendResult($result);
}
else
{
	echo Json::encode(array('ERROR' => 'UNKNOWN_ACTION'));
}

CMain::FinalActions();
die();