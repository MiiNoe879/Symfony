<?
define('CONFIRM_PAGE', true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Registration Confirmation");
?>
<?$APPLICATION->IncludeComponent(
	"thurly:system.auth.initialize",
	"",
	Array(
		"CHECKWORD_VARNAME"=>"checkword",
		"USERID_VARNAME"=>"user_id",
		"AUTH_URL"=>"/extranet/auth.php",
	),
false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>