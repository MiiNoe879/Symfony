<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"thurly:rest.apconnect", 
	".default", 
	array(
		'CLIENT_ID' => $_REQUEST["client_id"],
		'CLIENT_STATE' => $_REQUEST["state"],
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>