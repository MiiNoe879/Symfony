<?php
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/thurly/modules/main/include/prolog_before.php");

$result = array();
$request = Thurly\Main\Context::getCurrent()->getRequest();

if($USER->IsAuthorized() && $request->isPost() && check_thurly_sessid() && \Thurly\Main\Loader::includeModule('webservice'))
{
	$action = $request["action"];

	switch($action)
	{
		case 'stssync_auth':

			$ap = \Thurly\WebService\StsSync::getAuth($request['type']);
			if($ap)
			{
				$result = array('ap' => $ap);
			}

		break;
	}
}

Header('Content-Type: application/json;charset=utf-8');
echo \Thurly\Main\Web\Json::encode($result);

CMain::FinalActions();
