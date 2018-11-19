<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if(\Thurly\Main\Loader::includeModule('socialservices'))
{
	$dbRes = \Thurly\SocialServices\UserTable::getList(array(
		'filter' => array(
			'USER_ID' => $arParams['USER_ID'],
			'EXTERNAL_AUTH_ID' => CSocServThurlyOSNet::ID
		),
		'select' => array(
			'NAME', 'LAST_NAME', 'LOGIN', 'PERSONAL_WWW', 'XML_ID'
		),
	));

	$arResult['NETWORK_ACCOUNT'] = $dbRes->fetch();

	if(is_array($arResult['NETWORK_ACCOUNT']) && strlen($arResult['NETWORK_ACCOUNT']['PERSONAL_WWW']) <= 0)
	{
		$arResult['NETWORK_ACCOUNT']['PERSONAL_WWW'] = CSocServThurlyOSNet::NETWORK_URL.'/id'.$arResult['NETWORK_ACCOUNT']['XML_ID'].'/';
	}
}
?>