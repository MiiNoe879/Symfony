<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arResult['URI_DOMAIN'] = \Thurly\ImConnector\Connector::getDomainDefault();

if(defined('BX24_HOST_NAME'))
	$arResult['URI_DOMAIN_MOBILE'] = "thurlyos://" . BX24_HOST_NAME;
else
	$arResult['URI_DOMAIN_MOBILE'] = "thurlyos://" . \Thurly\Main\Context::getCurrent()->getServer()->getServerName();