<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/docs/shared/index.php");
$APPLICATION->SetTitle(GetMessage("DOCS_TITLE"));
$APPLICATION->AddChainItem($APPLICATION->GetTitle(), "/docs/shared/");
?>
<?$APPLICATION->IncludeComponent("thurly:disk.common", ".default", Array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#docs/shared",
		"STORAGE_ID" => "#SHARED_STORAGE_ID#"
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>