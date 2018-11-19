<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/docs/sale/index.php");
$APPLICATION->SetTitle(GetMessage("DOCS_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:disk.common", ".default", Array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#docs/sale",
		"STORAGE_ID" => "#SALE_STORAGE_ID#"
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>