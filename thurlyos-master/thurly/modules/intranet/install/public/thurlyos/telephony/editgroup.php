<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/telephony/editgroup.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("VI_PAGE_EDIT_GROUP_TITLE"));
?>

<?
$APPLICATION->IncludeComponent(
	"thurly:voximplant.queue.edit",
	"",
	array(
		'ID' => (int)$_REQUEST['ID']
	)
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
