<?php
/**
 * @global int $ID - Edited user id
 * @global string $strError - Save error
 * @global \CUser $USER
 * @global CMain $APPLICATION
 */

use Thurly\Main\Config\Option;
use Thurly\Main\Loader;
use Thurly\Socialservices\UserTable;

$ID = intval($ID);
$socialservices_res = true;

if(
	$ID > 0
	&& isset($_REQUEST["SS_REMOVE_NETWORK"])
	&& $_REQUEST["SS_REMOVE_NETWORK"] == "Y"
	&& Option::get("socialservices", "thurlyosnet_id", "") != ""
	&& Loader::includeModule('socialservices')
	&& check_thurly_sessid()
)
{
	$dbRes = UserTable::getList(array(
		'filter' => array(
			'=USER_ID' => $ID,
			'=EXTERNAL_AUTH_ID' => CSocServThurlyOSNet::ID
		),
		'select' => array('ID')
	));

	$profileInfo = $dbRes->fetch();
	if($profileInfo)
	{
		$deleteResult = UserTable::delete($profileInfo["ID"]);
		$socialservices_res = $deleteResult->isSuccess();

		if($socialservices_res)
		{
			\Thurly\Socialservices\Network::clearAdminPopupSession($ID);
		}
	}
}
