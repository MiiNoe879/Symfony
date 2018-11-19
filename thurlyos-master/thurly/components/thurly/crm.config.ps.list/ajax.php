<?php

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

use \Thurly\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if (!Loader::includeModule('crm') || !Loader::includeModule('sale'))
{
	return;
}

IncludeModuleLangFile(__FILE__);

global $DB, $APPLICATION;

$curUser = CCrmSecurityHelper::GetCurrentUser();
if (!$curUser || !$curUser->IsAuthorized() || !check_thurly_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	return;
}

\Thurly\Main\Localization\Loc::loadMessages(__FILE__);
CUtil::JSPostUnescape();
if (!function_exists('__CrmConfigPsEndResonse'))
{
	function __CrmActivityEditorEndResonse($result)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if (!empty($result))
			echo CUtil::PhpToJSObject($result);

		if (!defined('PUBLIC_AJAX_MODE'))
			define('PUBLIC_AJAX_MODE', true);

		require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');

		$APPLICATION->EpilogActions();
		die();
	}
}


$APPLICATION->RestartBuffer();
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
$action = isset($_POST['action']) ? $_POST['action'] : '';
if (strlen($action) == 0)
	__CrmActivityEditorEndResonse(array('ERROR' => 'Invalid data!'));

$result = array();

if ($action == 'active')
{
	$paySystemId = $_REQUEST['paySystemId'];
	$status = $_REQUEST['status'];

	$updateRes = \Thurly\Sale\PaySystem\Manager::update($paySystemId, array('ACTIVE' => $status));
	if (!$updateRes->isSuccess())
		$result['ERROR'] = implode(', ', $updateRes->getErrorMessages());

	if ($status == 'Y')
	{
		$data = \Thurly\Sale\PaySystem\Manager::getById($paySystemId);
		$personTypeList = \Thurly\Sale\PaySystem\Manager::getPersonTypeIdList($paySystemId);
		$personTypeId = array_shift($personTypeList);

		$sign = '';
		if ($data['ACTION_FILE'] == 'yandexreferrer' || $data['ACTION_FILE'] == 'yandexinvoice')
		{
			$sign = \Thurly\Sale\BusinessValue::get('YANDEX_SHOP_ID', 'PAYSYSTEM_'.$paySystemId, $personTypeId);
		}
		elseif ($data['ACTION_FILE'] == 'paypal')
		{
			$sign = \Thurly\Sale\BusinessValue::get('PAYPAL_USER', 'PAYSYSTEM_'.$paySystemId, $personTypeId);
		}

		if (!$sign)
			$result['ERROR'] = \Thurly\Main\Localization\Loc::getMessage('CRM_PS_DOES_NOT_CONFIG');
	}
}
elseif ($action == 'delete')
{
	$paySystemId = $_REQUEST['paySystemId'];

	$delRes = \Thurly\Sale\PaySystem\Manager::delete($paySystemId);
	if (!$delRes->isSuccess())
		$result['ERROR'] = implode(', ', $delRes->getErrorMessages());
}

__CrmActivityEditorEndResonse($result);