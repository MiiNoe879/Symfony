<?
use Thurly\Main\Page\Asset;

define("SKIP_MOBILEAPP_INIT", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
Asset::getInstance()->addString(Thurly\MobileApp\Mobile::getInstance()->getViewPort());
?>


<?$APPLICATION->IncludeComponent(
	"thurly:app.layout",
	".default",
	array(
		"ID" => $_GET["id"],
		"COMPONENT_TEMPLATE" => ".default",
		"MOBILE"=>"Y"
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>