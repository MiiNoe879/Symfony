<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["action"])>0 && check_thurly_sessid())
{
	$action = $_POST["action"];
	$gridId = $_POST["gridId"];
	$curOption = CUserOptions::GetOption("mobile.interface.grid", $gridId);

	if ($action == "fields")
	{
		$fields = $_POST["fields"];

		if (is_array($fields) && !empty($fields))
			$curOption["fields"] = $fields;
		else
			$curOption["fields"] = array();

		CUserOptions::SetOption("mobile.interface.grid", $gridId, $curOption);
	}
}
?>
