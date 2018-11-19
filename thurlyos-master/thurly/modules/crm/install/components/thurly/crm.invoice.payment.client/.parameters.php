<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!Thurly\Main\Loader::includeModule('crm'))
	return false;
if(!Thurly\Main\Loader::includeModule('sale'))
	return false;

Thurly\Main\Localization\Loc::loadMessages(__FILE__);

$paySystemList = array();
$paySystemByUser = Thurly\Sale\PaySystem\Manager::getHandlerList();

foreach($paySystemByUser as $user)
{
	$paySystemList = array_merge($paySystemList, $user);
}
$innerId = Thurly\Sale\PaySystem\Manager::getInnerPaySystemId();
unset($paySystemList[$innerId]);
	
$arComponentParameters = Array(
	'PARAMETERS' => array(		
		'EXCLUDED_ACTION_LIST' => array(
			'PARENT' => 'BASE',
			'NAME' => Thurly\Main\Localization\Loc::getMessage('CIPC_EXCLUDED_ACTION_LIST'),
			'TYPE' => 'LIST',
			'VALUES' => $paySystemList,
			"DEFAULT" => array('bill', 'billde', 'billen', 'billla', 'billua', 'billkz', 'billby', 'billbr', 'billfr'),
			"MULTIPLE"=>"Y",
			"SIZE" => 7,
			"COLS"=>25,
		),
	)	
);
?>