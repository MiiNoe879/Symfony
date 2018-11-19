<? require($_SERVER["DOCUMENT_ROOT"] . "/thurly/header.php");

$APPLICATION->IncludeComponent(
	'thurly:mobileapp.demoapi',
	'.default',
	array("DEMO_PAGE_ID" =>
		$_REQUEST["page"],
		"APP_DIR"=>"/#folder#/"
	),
	false,
	Array('HIDE_ICONS' => 'Y'));


require($_SERVER["DOCUMENT_ROOT"] . "/thurly/footer.php"); ?>