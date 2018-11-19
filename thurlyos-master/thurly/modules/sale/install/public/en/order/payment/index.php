<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("Order payment");
?><?$APPLICATION->IncludeComponent(
	"thurly:sale.order.payment",
	"",
	Array(
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");?>