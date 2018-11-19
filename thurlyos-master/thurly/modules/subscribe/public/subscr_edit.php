<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"thurly:subscribe.edit",
	"",
	Array(
		"SHOW_HIDDEN" => "N",
		"ALLOW_ANONYMOUS" => "Y",
		"SHOW_AUTH_LINKS" => "Y",
		"CACHE_TIME" => "3600",
		"SET_TITLE" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>