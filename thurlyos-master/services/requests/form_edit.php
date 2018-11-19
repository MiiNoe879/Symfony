<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Edit Order");
?>
<?$APPLICATION->IncludeComponent("thurly:form.result.edit", ".default", array(
	"RESULT_ID" => $_REQUEST["RESULT_ID"],
	"IGNORE_CUSTOM_TEMPLATE" => "N",
	"USE_EXTENDED_ERRORS" => "Y",
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "/services/requests/",
	"EDIT_ADDITIONAL" => "N",
	"EDIT_STATUS" => "N",
	"LIST_URL" => "my.php",
	"VIEW_URL" => "form_view.php",
	"CHAIN_ITEM_TEXT" => "",
	"CHAIN_ITEM_LINK" => ""
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>