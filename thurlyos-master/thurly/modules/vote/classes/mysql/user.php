<?
##############################################
# Thurly Site Manager Forum                  #
# Copyright (c) 2002-2009 Thurly             #
# http://www.thurlysoft.com                  #
# mailto:admin@thurlysoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/vote/classes/general/user.php");

class CVoteUser extends CAllVoteUser
{
	function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CVoteUser<br>File: ".__FILE__;
	}
}
?>