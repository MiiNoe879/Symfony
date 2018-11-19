<?php
if(!\Thurly\Main\Loader::includeModule('faceid'))
	return false;

if (is_object($APPLICATION))
	$APPLICATION->RestartBuffer();

\Thurly\FaceId\Log::write($_POST, 'PORTAL HIT');

$params = $_POST;
$hash = $params["BX_HASH"];
unset($params["BX_HASH"]);

// CLOUD HITS

if(
	$params['BX_TYPE'] == \Thurly\FaceId\Http::TYPE_THURLY24 && \Thurly\FaceId\Http::requestSign($params['BX_TYPE'], md5(implode("|", $params)."|".BX24_HOST_NAME)) === $hash ||
	$params['BX_TYPE'] == \Thurly\FaceId\Http::TYPE_CP && \Thurly\FaceId\Http::requestSign($params['BX_TYPE'], md5(implode("|", $params))) === $hash
)
{
	$params = \Thurly\Main\Text\Encoding::convertEncoding($params, 'UTF-8', SITE_CHARSET);

	$result = \Thurly\FaceId\Controller::receiveCommand($params['BX_COMMAND'], $params);
	if (is_null($result))
	{
		echo "You don't have access to this page.";
	}
	else
	{
		echo \Thurly\Main\Web\Json::encode($result);
	}
}
else
{
	echo "You don't have access to this page.";
}

CMain::FinalActions();
die();