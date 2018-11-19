<?php
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/mailtemplate/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));
$APPLICATION->IncludeComponent(
	'thurly:crm.mail_template', 
	'', 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/crm/configs/mailtemplate/",
	),
	false
); 
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>