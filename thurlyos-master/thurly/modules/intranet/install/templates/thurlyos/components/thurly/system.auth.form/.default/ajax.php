<?require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["action"])>0 && check_thurly_sessid())
{
	CUtil::JSPostUnescape();

	switch ($_POST["action"])
	{
		case "setView":
		{
			if (isset($_POST["currentStepId"]))
			{
				$currentStepId = $_POST["currentStepId"];

				$arViewedSteps = CUserOptions::GetOption("thurlyos", "new_helper_views", array());
				if (!in_array($currentStepId, $arViewedSteps))
				{
					$arViewedSteps[] = $currentStepId;
					CUserOptions::SetOption("thurlyos", "new_helper_views", $arViewedSteps);
				}
			}
			break;
		}
		case "setNotify":
		{
			$notify = CUserOptions::GetOption("thurlyos", "new_helper_notify", array());
			if (isset($_POST["time"]) && $_POST["time"] == "hour")
			{
				$notify["time"] = time() + 60*60;
			}
			elseif (isset($_POST["num"]))
			{
				$notify["num"] = intval($_POST["num"]);
				$notify["time"] = time() + 24*60*60;

				if ($notify["num"] == 0)//user has read all notifies
				{
					$notify["counter_update_date"] = time(); // time when user read all current notifications
				}
			}

			CUserOptions::SetOption("thurlyos", "new_helper_notify", $notify);
			break;
		}
		case "saveUserData":
		{
			if (isset($_POST["data"]))
			{
				$fields = array();

				if (isset($_POST["data"]["firstName"]))
				{
					$firstName = trim($_POST["data"]["firstName"]);
					if (!empty($firstName))
						$fields["NAME"] = $firstName;
				}

				if (isset($_POST["data"]["lastName"]))
				{
					$lastName = trim($_POST["data"]["lastName"]);
					if (!empty($lastName))
						$fields["LAST_NAME"] = $lastName;
				}

				if (isset($_POST["data"]["photo"]) && !empty($_POST["data"]["photo"]))
				{
					$fields["PERSONAL_PHOTO"] = CFile::MakeFileArray($_POST["data"]["photo"]);
				}

				global $USER;
				$res = $USER->Update($USER->GetID(), $fields);
			}

			CUserOptions::DeleteOption("thurlyos", "show_userinfo_spotlight");
			break;
		}
		case "delayUserSpotLight":
		{
			CUserOptions::SetOption("thurlyos", "show_userinfo_spotlight", array("needShow" => "Y", "time" => time() + 24*60*60));

			break;
		}
	}
	CMain::FinalActions();
	die();
}
?>