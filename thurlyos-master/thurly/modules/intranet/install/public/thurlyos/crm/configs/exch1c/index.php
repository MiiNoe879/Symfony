<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/exch1c/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));

$templateName = ".default";
if ($license_name = COption::GetOptionString("main", "~controller_group_name"))
{
	$f = preg_match("/(project|tf)$/is", $license_name, $matches);
	if (strlen($matches[0]) > 0)
		$templateName = "free";
}

$APPLICATION->IncludeComponent(
	"thurly:crm.config.exch1c",
	$templateName,
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/crm/configs/exch1c/",
		"PATH_TO_CONFIGS_INDEX" => "/crm/configs/"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>