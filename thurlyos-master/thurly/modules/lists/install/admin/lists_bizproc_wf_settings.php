<?
define("MODULE_ID", "lists");
if($_REQUEST['entity']=="ThurlyListsBizprocDocumentLists")
	define("ENTITY", 'Thurly\Lists\BizprocDocumentLists');
else
	define("ENTITY", "BizprocDocument");

$fp = $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/bizprocdesigner/admin/bizproc_wf_settings.php";
if (file_exists($fp))
	require($fp);