<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent(
	"thurly:bizproc.workflow.instances",
	"",
	array(
		"SET_TITLE" => 'Y',
		"NAME_TEMPLATE" => CSite::GetNameFormat(),
	)
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");