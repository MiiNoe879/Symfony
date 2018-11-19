<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('voximplant'))
	return;

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_SETTINGS, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
	return;

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_thurly_sessid())
{
	if (isset($_POST["BLACKLIST_SETTINGS_BUTTON"]))
	{
		Thurly\Main\Config\Option::set("voximplant", "blacklist_auto", (isset($_POST["BLACKLIST_AUTO"]) ? "Y" : "N"));

		$arBlacklistTime = (isset($_POST["BLACKLIST_TIME"]) && intval($_POST["BLACKLIST_TIME"]) && $_POST["BLACKLIST_TIME"] > 0) ? intval($_POST["BLACKLIST_TIME"]) : 5;
		Thurly\Main\Config\Option::set("voximplant", "blacklist_time", $arBlacklistTime);

		$arBlacklistCount = (isset($_POST["BLACKLIST_COUNT"]) && intval($_POST["BLACKLIST_COUNT"]) && $_POST["BLACKLIST_COUNT"] > 0) ? intval($_POST["BLACKLIST_COUNT"]) : 5;
		Thurly\Main\Config\Option::set("voximplant", "blacklist_count", $arBlacklistCount);

		Thurly\Main\Config\Option::set("voximplant", "blacklist_user_id", $USER->GetID());

		LocalRedirect(POST_FORM_ACTION_URI);
	}

	if (isset($_POST["BLACKLIST_ADD_BUTTON"]))
	{
		$newNumber = substr($_POST["BLACKLIST_NEW_NUMBER"], 0, 20);
		$newNumber = CVoxImplantPhone::Normalize($newNumber);
		if ($newNumber)
		{
			$dbBlacklist = Thurly\Voximplant\BlacklistTable::getList(array(
				"filter" => array("PHONE_NUMBER" => $newNumber)
			));
			if (!$dbBlacklist->Fetch())
			{
				Thurly\Voximplant\BlacklistTable::add(array(
					"PHONE_NUMBER" => $newNumber
				));
			}

			LocalRedirect(POST_FORM_ACTION_URI);
		}
		else
		{
			$arResult["ERROR"] = GetMessage("VI_BLACKLIST_NUMBER_ERROR");
		}
	}

	if (isset($_POST["action"]) && $_POST["action"] == "delete_number")
	{
		$dbBlacklist = Thurly\Voximplant\BlacklistTable::getList(array(
			"filter" => array("PHONE_NUMBER" => $_POST["number"])
		));
		if ($arBlacklist = $dbBlacklist->Fetch())
		{
			Thurly\Voximplant\BlacklistTable::delete($arBlacklist["ID"]);
		}
		$APPLICATION->RestartBuffer();
		echo \Thurly\Main\Web\Json::encode(array("success" => "Y"));
		die();
	}


}

$arResult["ITEMS"] = array();

$dbBlacklist = Thurly\Voximplant\BlacklistTable::getList();
while($arBlacklist = $dbBlacklist->Fetch())
{
	$arResult["ITEMS"][] = $arBlacklist;
}

$arResult["BLACKLIST_AUTO"] = Thurly\Main\Config\Option::get("voximplant", "blacklist_auto", "N");
$arResult["BLACKLIST_TIME"] = intval(Thurly\Main\Config\Option::get("voximplant", "blacklist_time", 5));
$arResult["BLACKLIST_COUNT"] = intval(Thurly\Main\Config\Option::get("voximplant", "blacklist_count", 5));

$this->IncludeComponentTemplate();
?>