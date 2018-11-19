<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/support/classes/general/reminder.php");

class CTicketReminder extends CAllTicketReminder
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." <br>Class: CTicketReminder<br>File: ".__FILE__;
	}
}
?>