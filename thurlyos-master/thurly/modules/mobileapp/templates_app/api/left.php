<?
require($_SERVER["DOCUMENT_ROOT"] . "/thurly/header.php");

$arParams = array(
	"MENU_FILE_PATH" => SITE_DIR . "/#folder#/.mobile_menu.php",
);
CMobile::getInstance()->setLargeScreenSupport(false);
CMobile::getInstance()->setScreenCategory("NORMAL");
$APPLICATION->IncludeComponent(
	'thurly:mobileapp.menu',
	'mobile',
	$arParams,
	false,
	Array('HIDE_ICONS' => 'Y'));
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/thurly/footer.php") ?>