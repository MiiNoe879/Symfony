<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/timeman/index.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?><?$APPLICATION->IncludeComponent("thurly:intranet.absence.calendar", ".default", Array(
	"FILTER_NAME"	=>	"absence",
	"FILTER_SECTION_CURONLY"	=>	"N"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>