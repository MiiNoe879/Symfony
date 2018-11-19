<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/vote_result.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:voting.result",
	"",
	Array(
		"VOTE_ID" => $_REQUEST["VOTE_ID"], 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "1200" 
	)
);?> 
<br />
 
<br />
 <a href="/services/votes.php"><?=GetMessage("SERVICES_LINK")?></a>
<br />
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>