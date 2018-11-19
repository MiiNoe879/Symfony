<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Thurly Framework
 * @package thurly
 * @subpackage mobile
 * @copyright 2001-2016 Thurly
 *
 * Thurly vars
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 */
/********************************************************************
				Input params
********************************************************************/
/************** URL ************************************************/
	$URL_NAME_DEFAULT = array(
			"USER" => "/company/personal/user/#USER_ID#/");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE) {
		$arParams["~PATH_TO_".$URL] = ($arParams["~PATH_TO_".$URL] ?: $URL_VALUE);
		$arParams["PATH_TO_".$URL] = htmlspecialcharsbx($arParams["~PATH_TO_".$URL]);
	}
/************** ADDITIONAL *****************************************/
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat());
/********************************************************************
				/Input params
********************************************************************/
if ($_REQUEST["VOTE_ID"] == $arParams["VOTE_ID"] && $_REQUEST["AJAX_RESULT"] == "Y" && check_thurly_sessid())
{
	$res = array(
		"COUNTER" => $arResult["VOTE"]["COUNTER"],
		"LAST_VOTE" => $arResult["LAST_VOTE"],
		"QUESTIONS" => array());
	foreach ($arResult["QUESTIONS"] as $arQuestion) {
		$res["QUESTIONS"][$arQuestion["ID"]] = array();
		foreach ($arQuestion["ANSWERS"] as $arAnswer){
			$res["QUESTIONS"][$arQuestion["ID"]][$arAnswer["ID"]] = array(
				"USERS" => $arAnswer["USERS"],
				"COUNTER" => $arAnswer["COUNTER"],
				"PERCENT" => (is_null($arAnswer["PERCENT"]) ? 0 : $arAnswer["PERCENT"]),
				"BAR_PERCENT" => $arAnswer["BAR_PERCENT"]);
			}
	}
	$APPLICATION->RestartBuffer();
	while(ob_end_clean());
	header('Content-Type:application/json; charset=UTF-8');
	?><?=\Thurly\Main\Web\Json::encode($res)?><?
	CMain::FinalActions();
	die();
}
?>