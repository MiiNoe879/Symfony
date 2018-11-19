<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//groups for thurlyos
if (isset($arResult['User']['GROUP_ID']))
{
	$arResult["GROUPS_LIST_HIDDEN"] = array();
	foreach($arResult['User']['GROUP_ID'] as $key => $group_id)
	{
		if($group_id != "1")
			$arResult["GROUPS_LIST_HIDDEN"][] = $group_id;

		$arResult["User"]["IS_ADMIN"] = in_array(1, $arResult['User']['GROUP_ID']) ? true : false;
	}
}

$arResult["User"]["IS_INVITED"] = !empty($arResult['User']["CONFIRM_CODE"]);

$arResult["IsMyProfile"] =  ($arResult["User"]["ID"] == $USER->GetID()) ? true: false;
$arResult["User"]["IS_EXTRANET"] = (
	empty($arResult["User"]['UF_DEPARTMENT'][0])
	&& \Thurly\Main\Loader::includeModule('extranet')
	&& \Thurly\Extranet\Util::checkExternalAuthId($arResult["User"]['EXTERNAL_AUTH_ID'])
		? true
		: false
);

$arResult['USER_PROP'] = array();

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
	{
		$arResult['USER_PROP'][$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? htmlspecialcharsbx($val["EDIT_FORM_LABEL"]) : $val["FIELD_NAME"]);

		$val['ENTITY_VALUE_ID'] = $arResult['User']['ID'];
		
		$val['VALUE'] = $arResult['User']["~".$val['FIELD_NAME']];
		$arResult['USER_PROPERTY_ALL'][$val['FIELD_NAME']] = $val;
	}
}

$arPolicy = CUser::GetGroupPolicy($arResult['User']['ID']);
$arResult["User"]["PASSWORD_REQUIREMENTS"] = is_array($arPolicy) && isset($arPolicy["PASSWORD_REQUIREMENTS"]) ? $arPolicy["PASSWORD_REQUIREMENTS"] : "";

$arResult["IS_THURLY24"] = false;
if (\Thurly\Main\Loader::includeModule("thurlyos"))
{
	$arResult["IS_THURLY24"] = true;
	$arResult["ADMIN_RIGHTS_RESTRICTED"] = false;
	$arResult["IS_COMPANY_TARIFF"] = false;

	if (!$arResult['User']['IS_ADMIN'])
	{
		$arResult["ADMIN_LIMITS_ENABLED"] = COption::GetOptionString("thurlyos", "admin_limits_enabled", "N") == "Y" ? true : false;
		if ($arResult["ADMIN_LIMITS_ENABLED"])
		{
			$numAdmins = 1;
			$curAdmins = \CThurlyOS::getAllAdminId();
			if (is_array($curAdmins))
			{
				$numAdmins = count($curAdmins);
			}

			$maxAdmins = \CThurlyOS::getMaxAdminCount();
			if ($maxAdmins > 0 && $numAdmins >= $maxAdmins)
			{
				$arResult["ADMIN_RIGHTS_RESTRICTED"] = true;
			}

			if ($arResult["ADMIN_RIGHTS_RESTRICTED"])
			{
				CThurlyOS::initLicenseInfoPopupJS();

				$licenseType = \CThurlyOS::getLicenseType();
				if ($licenseType == "company")
				{
					$arResult["IS_COMPANY_TARIFF"] = true;
				}
			}
		}
	}
}
?>