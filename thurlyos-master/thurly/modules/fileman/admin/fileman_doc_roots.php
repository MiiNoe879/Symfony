<?
##############################################
# Thurly: SiteManager                        #
# Copyright (c) 2002-2006 Thurly             #
# http://www.thurlysoft.com                  #
# mailto:admin@thurlysoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if ($FM_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/fileman/prolog.php");

$APPLICATION->SetTitle(GetMessage("FILEMAN_DOC_ROOT_TITLE"));
if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_after.php");

$adminPage->ShowSectionIndex("menu_fileman_file_", "fileman");

if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin.php");
?>