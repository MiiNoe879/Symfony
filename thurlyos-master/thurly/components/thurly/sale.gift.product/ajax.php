<?php

if (empty($_POST['parameters']))
{
	echo 'no parameters found';
	return;
}

if (isset($_REQUEST['site_id']) && !empty($_REQUEST['site_id']))
{
	if (!is_string($_REQUEST['site_id']))
		die();
	if (preg_match('/^[a-z0-9_]{2}$/i', $_REQUEST['site_id']) === 1)
		define('SITE_ID', $_REQUEST['site_id']);
}

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$signer = new \Thurly\Main\Security\Sign\Signer;

try
{
	$parameters = $signer->unsign($_POST['parameters'], 'bx.sale.gift.product');
	$template = $signer->unsign($_POST['template'], 'bx.sale.gift.product');
}
catch (\Thurly\Main\Security\Sign\BadSignatureException $e)
{
	die();
}

$APPLICATION->IncludeComponent(
	"thurly:sale.gift.product",
	$template,
	unserialize(base64_decode($parameters)),
	false
);