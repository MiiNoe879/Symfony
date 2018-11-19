<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Orders");
?><?$APPLICATION->IncludeComponent("thurly:sale.personal.order", ".default", Array(
	"SEF_MODE"	=>	"N",
	"ORDERS_PER_PAGE"	=>	"20",
	"PATH_TO_PAYMENT"	=>	"/personal/order/payment/",
	"PATH_TO_BASKET"	=>	"/personal/cart/",
	"SET_TITLE"	=>	"Y",
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>