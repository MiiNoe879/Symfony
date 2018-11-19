<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent("thurly:learning.search", ".default", array(
	"PAGE_RESULT_COUNT" => "10",
	"DISPLAY_TOP_PAGER" => "Y",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	),
	$component
);
;?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>