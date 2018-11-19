<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent(
	"thurly:learning.student.profile",
	"",
	Array(
		"TRANSCRIPT_DETAIL_TEMPLATE" => "certification/?TRANSCRIPT_ID=#TRANSCRIPT_ID#", 
		"SET_TITLE" => "Y" 
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>