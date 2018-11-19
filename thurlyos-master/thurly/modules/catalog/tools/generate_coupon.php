<?
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
define('NO_AGENT_CHECK', true);

use Thurly\Main\Localization\Loc;
use Thurly\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

$arResult = array(
	'STATUS' => 'OK',
	'MESSAGE' => '',
	'RESULT' => '',
);
$boolFlag = true;

Loc::loadMessages(__FILE__);

if ($boolFlag)
{
	if (!isset($USER) || !($USER instanceof CUser))
	{
		$arResult['STATUS'] = 'ERROR';
		$arResult['MESSAGE'] = Loc::getMessage('BT_CAT_TOOLS_GEN_CPN_ERR_USER');
		$boolFlag = false;
	}
	elseif (!$USER->IsAuthorized())
	{
		$arResult['STATUS'] = 'ERROR';
		$arResult['MESSAGE'] = Loc::getMessage('BT_CAT_TOOLS_GEN_CPN_ERR_AUTH');
		$boolFlag = false;
	}
}

if ($boolFlag)
{
	if (!check_thurly_sessid())
	{
		$arResult['STATUS'] = 'ERROR';
		$arResult['MESSAGE'] = Loc::getMessage('BT_CAT_TOOLS_GEN_CPN_ERR_SESSION');
		$boolFlag = false;
	}
}
if ($boolFlag)
{
	if (!$USER->CanDoOperation('catalog_discount'))
	{
		$arResult['STATUS'] = 'ERROR';
		$arResult['MESSAGE'] = Loc::getMessage('BT_CAT_TOOLS_GEN_CPN_ERR_RIGHTS');
		$boolFlag = false;
	}
}

if ($boolFlag)
{
	if (Loader::includeModule('catalog'))
	{
		do
		{
			$strCoupon = substr(CatalogGenerateCoupon(), 0, 32);
			$boolCheck = !CCatalogDiscountCoupon::IsExistCoupon($strCoupon);
		}
		while (!$boolCheck);
		$arResult['RESULT'] = $strCoupon;
	}
	else
	{
		$arResult['STATUS'] = 'ERROR';
	}
}

echo CUtil::PhpToJSObject($arResult);