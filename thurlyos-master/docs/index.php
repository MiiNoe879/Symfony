<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
if(!\Thurly\Main\Loader::includeModule('disk'))
	return;
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/docs/index.php");
$APPLICATION->SetTitle(GetMessage("DOCS_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:disk.aggregator",
	"",
	Array(
		"SEF_MODE" => "Y",
		"CACHE_TIME" => 3600,
		"SEF_FOLDER" => "/docs/all",
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
