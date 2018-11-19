<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Catalog\SubscribeTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if(!check_thurly_sessid() || !Loader::includeModule('catalog')) die();

Loc::loadMessages(__FILE__);

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['deleteSubscribe'] == 'Y')
{
	if(empty($_POST['listSubscribeId']) || !is_array($_POST['listSubscribeId']))
	{
		echo Thurly\Main\Web\Json::encode(array('error' => true));
		die();
	}
	try
	{
		$subscribeManager = new \Thurly\Catalog\Product\SubscribeManager;
		if(!$subscribeManager->deleteManySubscriptions($_POST['listSubscribeId'], $_POST['itemId']))
		{
			$errorObject = current($subscribeManager->getErrors());
			if($errorObject)
			{
				echo Thurly\Main\Web\Json::encode(array('error' => true,
					'message' => $errorObject->getMessage()));
				die();
			}
		}

		echo Thurly\Main\Web\Json::encode(array('success' => true));
		die();
	}
	catch(Exception $e)
	{
		echo Thurly\Main\Web\Json::encode(array('error' => true, 'message' => $e->getMessage()));
		die();
	}
}

echo Thurly\Main\Web\Json::encode(array());
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
die();