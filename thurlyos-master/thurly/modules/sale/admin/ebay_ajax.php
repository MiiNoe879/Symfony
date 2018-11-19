<?
/** Thurly Framework
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

$arResult = array();

use \Thurly\Sale\TradingPlatform\Logger;
use \Thurly\Sale\TradingPlatform\Ebay\Ebay;
use \Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (!\Thurly\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = "Module sale is not installed!";

$result = false;

if(isset($arResult["ERROR"]) <= 0 && $APPLICATION->GetGroupRight("sale") >= "W" && check_thurly_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';
	$siteId = isset($_REQUEST['siteId']) ? trim($_REQUEST['siteId']): '';

	switch ($action)
	{
		case "startFeed":

			$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']): '';
			$startPosition = isset($_REQUEST['startPos']) ? trim($_REQUEST['startPos']) : '';

			try
			{
				$ebayFeed = \Thurly\Sale\TradingPlatform\Ebay\Feed\Manager::createFeed($type, $siteId);
				$ebayFeed->processData($startPosition);

				if($type != "ORDER")
				{
					$queue = \Thurly\Sale\TradingPlatform\Ebay\Feed\Manager::createSftpQueue($type, $siteId);
					$queue->sendData();
				}

				$arResult["COMPLETED"] = true;
			}
			catch(\Thurly\Sale\TradingPlatform\TimeIsOverException $e)
			{
				$arResult["END_POS"] = $e->getEndPosition();
			}
			catch(\Exception $e)
			{
				Ebay::log(Logger::LOG_LEVEL_ERROR, "EBAY_FEED_ERROR", $type, $e->getMessage(), $siteId);
				$arResult["ERROR"] = $e->getMessage();
			}

			break;

		case "refreshCategoriesData":
			try
			{
				$categories = new \Thurly\Sale\TradingPlatform\Ebay\Api\Categories($siteId);
				$arResult["COUNT"] = $categories->refreshTableData();
			}
			catch(\Thurly\Main\SystemException $e)
			{
				$arResult["ERROR"] = $e->getMessage();
			}

			break;

		case "refreshCategoriesPropsData":
			try
			{
				$categoriesProps = new \Thurly\Sale\TradingPlatform\Ebay\Api\Categories($siteId);
				$arResult["COUNT"] = $categoriesProps->refreshVariationsTableData();
			}
			catch(\Thurly\Main\SystemException$e)
			{
				$arResult["ERROR"] = $e->getMessage();
			}

			break;

		default:
			$arResult["ERROR"] = "Wrong action";
			break;
	}
}
else
{
	if(strlen($arResult["ERROR"]) <= 0)
		$arResult["ERROR"] = "Access denied";
}

if(isset($arResult["ERROR"]))
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));