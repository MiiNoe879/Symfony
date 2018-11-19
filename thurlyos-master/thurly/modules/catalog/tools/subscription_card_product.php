<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

use Thurly\Main,
	Thurly\Main\Localization\Loc,
	Thurly\Main\Loader,
	Thurly\Catalog;

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);

$error = false;
$errorMessage = '';
if(!$USER->canDoOperation('catalog_read') && !$USER->canDoOperation('catalog_view'))
{
	$error = true;
	$errorMessage = Loc::getMessage('CSD_ACCESS_DENIED');
}
if(!check_thurly_sessid())
{
	$error = true;
	$errorMessage = Loc::getMessage('CSD_INCORRECT_SESSION');
}
if(!Loader::includeModule('catalog'))
{
	$error = true;
	$errorMessage = Loc::getMessage('CSD_MODULE_NOT_INSTALLED', array('#NAME#' => 'catalog'));
}
if($error)
{
	echo Thurly\Main\Web\Json::encode(array('error' => true, 'message' => $errorMessage));
	require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_admin_after.php');
	die();
}

$request = Main\Context::getCurrent()->getRequest();
$totalCount = 0;
$activeCount = 0;

if ($request->getRequestMethod() == 'POST' && $request['getSubscriptionData'] == 'Y')
{
	try
	{
		$totalCount = Catalog\SubscribeTable::getList(array(
			'select' => array('CNT'),
			'filter' => array('=ITEM_ID' => intval($request['itemId'])),
			'runtime' => array(new Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
		))->fetch();
		$totalCount = $totalCount['CNT'];
		global $DB;
		$activeCount = Catalog\SubscribeTable::getList(array(
			'select' => array('CNT'),
			'filter' => array(
				'=ITEM_ID' => intval($request['itemId']),
				array(
					'LOGIC' => 'OR',
					array('=DATE_TO' => false),
					array('>DATE_TO' => date($DB->dateFormatToPHP(CLang::getDateFormat('FULL')), time()))
				)
			),
			'runtime' => array(new Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
		))->fetch();
		$activeCount = $activeCount['CNT'];

		echo Thurly\Main\Web\Json::encode(
			array('success' => true, 'data' => array('totalCount' => $totalCount, 'activeCount' => $activeCount)));
		require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_admin_after.php');
		die();
	}
	catch(Main\SystemException $exception)
	{
		echo Thurly\Main\Web\Json::encode(array('error' => true, 'message' => $exception->getMessage()));
		require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_admin_after.php');
		die();
	}
}
?>
