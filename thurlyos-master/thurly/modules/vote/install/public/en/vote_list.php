<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Votes");
?>

<?$APPLICATION->IncludeComponent(
	"thurly:voting.list",
	"",
	Array(
		"CHANNEL_SID" => "ANKETA", 
		"VOTE_FORM_TEMPLATE" => "vote_new.php?VOTE_ID=#VOTE_ID#", 
		"VOTE_RESULT_TEMPLATE" => "vote_result.php?VOTE_ID=#VOTE_ID#", 
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
