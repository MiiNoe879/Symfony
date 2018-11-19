<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"thurly:sale.basket.basket",
	"",
	Array(
		"PATH_TO_ORDER" => "order.php", 
		"HIDE_COUPON" => "N", 
		"COLUMNS_LIST" => Array("NAME","PRICE","TYPE","QUANTITY","DELETE","DELAY","WEIGHT"), 
		"SET_TITLE" => "Y" 
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>