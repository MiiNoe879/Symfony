<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

if(!\Thurly\Main\Loader::includeModule("sender"))
	ShowError(\Thurly\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$arResult = array('COUNT' => '0');

if(isset($CONNECTOR_SETTING) && is_array($CONNECTOR_SETTING))
{
	$arEndpointList = \Thurly\Sender\ConnectorManager::getEndpointFromFields($CONNECTOR_SETTING);
	foreach ($arEndpointList as $endpoint)
	{
		$connector = \Thurly\Sender\ConnectorManager::getConnector($endpoint);
		if ($connector)
		{
			$connector->setFieldValues($endpoint['FIELDS']);
			$arResult['COUNT'] = $connector->getDataCount();
			break;
		}
	}
}

echo CUtil::PhpToJSObject($arResult);
exit();