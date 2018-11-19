<?
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["action"])>0 && check_thurly_sessid())
{
	if ($_POST["action"] == "delete" && CModule::IncludeModule("rest"))
	{
		$APPLICATION->RestartBuffer();

		Header('Content-Type: application/json');

		$res = false;
		$apId = 0;

		if (isset($_POST["apId"]) && intval($_POST["apId"]))
		{
			$apId = intval($_POST["apId"]);
		}

		if($apId > 0)
		{
			$dbRes = \Thurly\Rest\APAuth\PasswordTable::getByPrimary($apId);
			$ap = $dbRes->fetch();

			if($ap && $ap['USER_ID'] == $USER->GetID())
			{
				$result = \Thurly\Rest\APAuth\PasswordTable::delete($ap['ID']);

				if(!$result->isSuccess())
				{
					echo \Thurly\Main\Web\Json::encode(array("error" => "Y"));
				}
				else
				{
					echo \Thurly\Main\Web\Json::encode(array("success" => "Y"));
				}
			}
			else
			{
				echo \Thurly\Main\Web\Json::encode(array("error" => "Y"));
			}
		}

	}
}

CMain::FinalActions();
?>
