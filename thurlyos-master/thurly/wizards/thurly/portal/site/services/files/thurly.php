<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(strlen(rtrim($_SERVER["DOCUMENT_ROOT"], "/")) <= strlen(rtrim(WIZARD_SITE_PATH, '/')))
	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/thurly/",
		WIZARD_SITE_PATH."/thurly/",
		$rewrite = false,
		$recursive = true,
		$exclude = "urlrewrite.php"
	);
?>