<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/learning/classes/general/test.php");

class CTest extends CAllTest
{
	function GetRandFunction()
	{
		return " RAND(".rand(0, 1000000).") ";
	}
}
