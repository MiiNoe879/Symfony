<?
/*
##############################################
# Thurly: SiteManager                        #
# Copyright (c) 2002 Thurly                  #
# http://www.thurly.ru                       #
# mailto:admin@thurly.ru                     #
##############################################
*/
define("STOP_STATISTICS", "Y");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/workflow/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/workflow/include.php");

$fname = $_REQUEST["fname"];
if ($APPLICATION->GetGroupRight("workflow")>="R")
{
	session_write_close();
	$src = CWorkflow::GetFileContent($did, $fname, $wf_path, $site);
	$ext = strtolower(GetFileExtension($fname));
	$arrExt = explode(",", strtolower(CFile::GetImageExtensions()));
	if(in_array($ext, $arrExt))
	{
		if ($ext=="jpg") $ext = "jpeg";
		header("Content-type: image/".$ext);
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Pragma: public");
		echo $src;
		die();
	}
	echo TxtToHtml($src);
}
die();
?>