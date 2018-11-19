<?php
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/mailtemplate/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
$APPLICATION->IncludeComponent(
	'thurly:crm.mail_template', 
	'', 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/configs/mailtemplate/",
	),
	false
); 
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>