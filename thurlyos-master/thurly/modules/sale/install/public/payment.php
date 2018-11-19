<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"thurly:sale.order.payment",
	"",
	Array(
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>