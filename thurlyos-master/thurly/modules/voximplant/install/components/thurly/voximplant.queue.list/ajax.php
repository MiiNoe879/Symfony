<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("voximplant"))
{
	echo CUtil::PhpToJsObject(Array('SUCCESS' => false, 'ERROR' => 'VI_MODULE_NOT_INSTALLED'));
	CMain::FinalActions();
	die();
}

if (!check_thurly_sessid())
{
	echo CUtil::PhpToJsObject(Array('SUCCESS' => false, 'ERROR' => 'SESSION_ERROR'));
	CMain::FinalActions();
	die();
}

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_SETTINGS,\Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
{
	echo CUtil::PhpToJsObject(Array('SUCCESS' => false, 'ERROR' => 'AUTHORIZE_ERROR'));
	CMain::FinalActions();
	die();
}

$action = $_REQUEST['action'];
if($action == 'delete')
{
	$queueId = (int)$_POST['id'];
	$queue = \Thurly\Voximplant\Queue::createWithId($queueId);
	$usages = ($queue instanceof \Thurly\Voximplant\Queue) ? $queue->findUsages() : 0;
	if(count($usages) > 0)
	{
		$result = array(
			'SUCCESS' => false,
			'USAGES' => $usages
		);
	}
	else
	{
		//don't delete yet
		\Thurly\Voximplant\Model\QueueTable::delete($queueId);
		$result = array('SUCCESS' => true);
	}

	echo\Thurly\Main\Web\Json::encode($result);
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'UNKNOWN_ACTION'));
}

CMain::FinalActions();
die();