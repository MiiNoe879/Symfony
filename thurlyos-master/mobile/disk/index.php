<?require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
if(!empty($_GET['download']))
{
	define("EXTRANET_NO_REDIRECT", true);
}
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');
?>
<?$APPLICATION->IncludeComponent(
	"thurly:mobile.disk.file.detail",
	".default",
	array(
	),
	false,
	array("HIDE_ICONS" => "Y")
);
?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');?>