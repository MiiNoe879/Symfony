<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

CopyDirFiles(
	WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/thurly/",
	WIZARD_SITE_PATH."/thurly/",
	$rewrite = false, 
	$recursive = true
);

?>
