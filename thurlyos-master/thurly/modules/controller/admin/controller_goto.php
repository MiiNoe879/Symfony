<?
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
$member_id = intval($_REQUEST['member']);

if (!CModule::IncludeModule("controller"))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bCanAuthorize = false;
$bAsAdmin = false;
if ($USER->CanDoOperation("controller_member_auth_admin"))
{
	$bCanAuthorize = true;
	$bAsAdmin = true;
}
elseif ($USER->CanDoOperation("controller_member_auth"))
{
	$bCanAuthorize = true;
	$bAsAdmin = false;
}
elseif ($member_id > 0 && $USER->IsAuthorized())
{
	$grantList = \Thurly\Controller\AuthGrantTable::getActiveForControllerMember($member_id, $USER->GetID(), $USER->GetUserGroupArray());
	while ($grant = $grantList->fetch())
	{
		if ($grant["SCOPE"] === "user")
		{
			$bCanAuthorize = true;
		}
		elseif ($grant["SCOPE"] === "admin")
		{
			$bCanAuthorize = true;
			$bAsAdmin = true;
		}
	}
}

if (!$bCanAuthorize)
{
	LocalRedirect("/thurly/admin/controller_member_admin.php");
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/controller/prolog.php");

IncludeModuleLangFile(__FILE__);

$dbr = CControllerMember::GetByID($member_id);
$ar = $dbr->GetNext();
if (!$ar)
{
	LocalRedirect("/thurly/admin/controller_member_admin.php");
}

if ($bAsAdmin)
{//Authorize as admin
	$param = 'Array(
		"LOGIN"=>"'.EscapePHPString($USER->GetParam("LOGIN")).'",
		"NAME"=>"'.EscapePHPString($USER->GetParam("FIRST_NAME")).'",
		"LAST_NAME"=>"'.EscapePHPString($USER->GetParam("LAST_NAME")).'",
		"EMAIL"=>"'.EscapePHPString($USER->GetParam("EMAIL")).'",
	)';
	$query = '
	CControllerClient::AuthorizeAdmin('.$param.');
	LocalRedirect("/");
	';
	$arControllerLog = Array(
		'NAME' => 'AUTH',
		'CONTROLLER_MEMBER_ID' => $ar["ID"],
		'DESCRIPTION' => GetMessage("CTRLR_LOG_GOADMIN").' ('.$USER->GetParam("LOGIN").')',
		'STATUS' => 'Y',
	);
}
else
{//Authorize as user
	$arGroups = array();
	$arUserGroups = $USER->GetUserGroupArray();
	$arLocGroups = \Thurly\Controller\GroupMapTable::getMapping("CONTROLLER_GROUP_ID", "REMOTE_GROUP_CODE");
	foreach ($arLocGroups as $arTGroup)
	{
		foreach ($arUserGroups as $group_id)
		{
			if ($arTGroup["FROM"] == $group_id)
				$arGroups[] = EscapePHPString($arTGroup["TO"]);
		}
	}

	if (count($arGroups) > 0)
		$strGroups = '"GROUP_ID" => Array("'.implode('", "', $arGroups).'"),';
	else
		$strGroups = '';

	$param = 'Array(
		'.$strGroups.'
		"LOGIN"=>"'.EscapePHPString($USER->GetParam("LOGIN")).'",
		"NAME"=>"'.EscapePHPString($USER->GetParam("FIRST_NAME")).'",
		"LAST_NAME"=>"'.EscapePHPString($USER->GetParam("LAST_NAME")).'",
		"EMAIL"=>"'.EscapePHPString($USER->GetParam("EMAIL")).'",
	)';
	$query = '
	CControllerClient::AuthorizeUser('.$param.');
	LocalRedirect("/");
	';
	$arControllerLog = Array(
		'NAME' => 'AUTH',
		'CONTROLLER_MEMBER_ID' => $ar["ID"],
		'DESCRIPTION' => GetMessage("CTRLR_LOG_GOUSER").' ('.$USER->GetParam("LOGIN").')',
		'STATUS' => 'Y',
	);
}

CControllerLog::Add($arControllerLog);
if (\Thurly\Controller\AuthLogTable::isEnabled())
{
	\Thurly\Controller\AuthLogTable::logControllerToSiteAuth(
		$ar["ID"],
		$USER->GetID(),
		true,
		'CONTROLLER_GOTO',
		$USER->GetParam("FIRST_NAME").' '.$USER->GetParam("LAST_NAME").' ('.$USER->GetParam("LOGIN").')'
	);
}

$result = CControllerMember::RunCommandRedirect($ar["ID"], $query, array(), false);
if ($result !== false)
{
	LocalRedirect($ar["URL"]."/thurly/main_controller.php?lang=".LANGUAGE_ID, true);
}
else
{
	$e = $APPLICATION->GetException();
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_after.php");
	ShowError("Error: ".$e->GetString());
	?>
	<a href="/thurly/admin/controller_member_admin.php?lang=<?=LANGUAGE_ID?>"><? echo GetMessage("CTRLR_GOTO_BACK") ?></a>
	<?
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin.php");
}
