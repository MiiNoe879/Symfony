<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent(
	"thurly:learning.student.transcript",
	"",
	Array(
		"TRANSCRIPT_ID" => $_REQUEST["TRANSCRIPT_ID"], 
		"SET_TITLE" => "Y" 
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>