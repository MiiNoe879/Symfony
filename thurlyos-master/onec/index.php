<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent(
	"thurly:crm.1c.start",
	$templateName,	
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/onec/"		
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>