<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!WIZARD_IS_INSTALLED)
{
	if (IsModuleInstalled("thurlyos"))
	{
		$file = fopen(WIZARD_SITE_ROOT_PATH."/thurly/php_interface/dbconn.php", "ab");
		fwrite($file, file_get_contents(WIZARD_ABSOLUTE_PATH."/site/services/files/thurly/dbconn.php"));
		fclose($file);
	}

	CopyDirFiles(
		$_SERVER['DOCUMENT_ROOT'].'/thurly/modules/intranet/install/public/pub/',
		WIZARD_SITE_PATH."/pub/",
		$rewrite = true,
		$recursive = true,
		$delete_after_copy = false
	);

	CopyDirFiles(
		$_SERVER['DOCUMENT_ROOT']."/thurly/modules/intranet/install/public/thurlyos/",
		WIZARD_SITE_PATH,
		$rewrite = true,
		$recursive = true,
		$delete_after_copy = false
	);
}