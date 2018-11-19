<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Vote results");
$APPLICATION->AddChainItem("Votes", "vote_list.php");
?>
<?$APPLICATION->IncludeComponent("thurly:voting.result", ".default", Array(
	"VOTE_ID"	=> $_REQUEST["VOTE_ID"],
	)
);?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>
