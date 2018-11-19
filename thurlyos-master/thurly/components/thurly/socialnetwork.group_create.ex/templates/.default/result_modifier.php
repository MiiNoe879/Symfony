<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CThurlyComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arResult['TypesProject'] = array();
$arResult['TypesNonProject'] = array();
$arResult['ClientConfig'] = array(
	'refresh' => (empty($_GET["refresh"]) || $_GET["refresh"] != 'N' ? 'Y' : 'N')
);

foreach($arResult['Types'] as $code => $type)
{
	if ($type['PROJECT'] == 'Y')
	{
		$arResult['TypesProject'][$code] = $type;
	}
	else
	{
		$arResult['TypesNonProject'][$code] = $type;
	}
}

$arResult['openAdditional'] = false;

$arResult["GROUP_PROPERTIES_MANDATORY"] = $arResult["GROUP_PROPERTIES_NON_MANDATORY"] = array();
if (!empty($arResult["GROUP_PROPERTIES"]))
{
	foreach($arResult["GROUP_PROPERTIES"] as $key => $userField)
	{
		if ($userField["MANDATORY"] == "Y")
		{
			$arResult["GROUP_PROPERTIES_MANDATORY"][$key] = $userField;
		}
		else
		{
			$arResult["GROUP_PROPERTIES_NON_MANDATORY"][$key] = $userField;
		}
	}
}

$arResult["TypeRowNameList"] = array(
	"Project" => Loc::getMessage('SONET_GCE_T_TYPE_SUBTITLE_PROJECT'),
	"NonProject" => Loc::getMessage('SONET_GCE_T_TYPE_SUBTITLE_GROUP')
);

$arResult["TypeRowList"] = (
	!empty($arParams["FIRST_ROW"])
	&& $arParams["FIRST_ROW"] == "project"
		? array("TypesProject", "TypesNonProject")
		: array("TypesNonProject", "TypesProject")
);
?>