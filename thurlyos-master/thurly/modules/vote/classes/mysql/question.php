<?
##############################################
# Thurly Site Manager Forum                  #
# Copyright (c) 2002-2009 Thurly             #
# http://www.thurlysoft.com                  #
# mailto:admin@thurlysoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/vote/classes/general/question.php");

class CVoteQuestion extends CAllVoteQuestion
{
	function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CVoteQuestion<br>File: ".__FILE__;
	}
}
?>