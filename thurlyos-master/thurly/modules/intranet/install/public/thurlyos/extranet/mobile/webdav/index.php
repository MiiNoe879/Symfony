<?require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');
?>
<?$APPLICATION->IncludeComponent(
	"thurly:mobile.webdav.file.list",
	".default",
	Array(
		"NAME_FILE_PROPERTY" => "FILE",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => SITE_DIR."mobile/webdav"
	),
	false
);
?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');?>