<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/info/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?> 
<?=GetMessage("CRM_INFO")?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>