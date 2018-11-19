<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var $arParams array
 * @var $arResult array
 * @var $component CThurlyComponent
 */
if (CModule::IncludeModule("vote"))
{
	if (class_exists("\\Thurly\\Vote\\UF\\Manager"))
	{
		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			"thurly:voting.uf",
			".default",
			array(
				"PARAMS" => $arParams,
				"RESULT" => $arResult
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}
	else
	{
		$arComponentParams = array(
			"CHANNEL_ID" => $arParams["~arUserField"]["SETTINGS"]["CHANNEL_ID"],
			"VOTE_ID" => $arParams["~arUserField"]["VALUE"],
			"NAME_TEMPLATE" => $arParams["~arAddField"]["NAME_TEMPLATE"],
			"PATH_TO_USER" => $arParams["~arAddField"]["PATH_TO_USER"]
		);

		if (isset($arParams["ACTION_PAGE"]))
		{
			$arComponentParams["ACTION_PAGE"] = $arParams["ACTION_PAGE"];
		}

		$GLOBALS["APPLICATION"]->IncludeComponent(
			"thurly:voting.current",
			".userfield",
			$arComponentParams,
			null,
			array("HIDE_ICONS" => "Y")
		);
	}
}
?>