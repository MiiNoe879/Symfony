<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
$GLOBALS['APPLICATION']->ShowHead();
CUtil::InitJSCore(array('fx'));
if (intval($_GET['id']) > 0 && strlen($_GET['name']) > 0)
{
	$GLOBALS['APPLICATION']->IncludeComponent(
		'thurly:advertising.banner',
		htmlspecialcharsbx($_GET['name']),
		array(
			'QUANTITY' => 1,
			'BANNER_ID' => intval($_GET['id']),
			'PREVIEW' => 'Y'
		)
	);
}