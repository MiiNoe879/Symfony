<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"thurly:sale.personal.order",
	".default",
	Array(
		"SEF_MODE" => "N", 
		"ORDERS_PER_PAGE" => "20", 
		"SET_TITLE" => "Y" 
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
