<?
define("MODULE_ID", "disk");
if($_REQUEST['entity']=="ThurlyDiskBizProcDocument" || $_REQUEST['entity']=="Thurly\\Disk\\BizProcDocument")
{
	define("ENTITY", "Thurly\\Disk\\BizProcDocument");
}
else
{
	define("ENTITY", "Thurly\\Disk\\BizProcDocumentCompatible");
}
$fp = $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/bizproc/admin/bizproc_selector.php";
if(file_exists($fp))
	require($fp);