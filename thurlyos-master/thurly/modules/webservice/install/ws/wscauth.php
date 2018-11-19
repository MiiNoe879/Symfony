<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
?><?$APPLICATION->IncludeComponent(
	"thurly:webservice.checkauth",
	"",
	Array(
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>