<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Account");
?><?$APPLICATION->IncludeComponent(
	"thurly:sale.personal.account",
	"",
	Array(
		"SET_TITLE" => "Y" 
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>