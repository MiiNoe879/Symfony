<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/support/classes/general/update.php");

class CSupportUpdate extends CAllSupportUpdate
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." <br>Class: CSupportUpdate<br>File: ".__FILE__;
	}
	
	function GetBD()
	{	
		return "MySQL";// "MySQL","MSSQL","Oracle"
	}
	
	
}

?>