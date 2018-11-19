<?
/**
 * Thurly Framework
 * @global CMain $APPLICATION
 */

use Thurly\Main\Localization\Loc;
use Thurly\Sale\Delivery\Services;
use Thurly\Sale\Delivery\Restrictions;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

$lang = isset($_REQUEST['lang']) ? trim($_REQUEST['lang']) : "ru";
\Thurly\Main\Context::getCurrent()->setLanguage($lang);

Loc::loadMessages(__FILE__);

$arResult = array("ERROR" => "");

if (!\Thurly\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = "Error! Can't include module \"Sale\"";

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/lib/delivery/inputs.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(strlen($arResult["ERROR"]) <= 0 && $saleModulePermissions >= "W" && check_thurly_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "get_group_dialog_content":
			$selectedGroupId = isset($_REQUEST['selectedGroupId']) ? trim($_REQUEST['selectedGroupId']): '';
			die(
				"<table><tr><td>".
					\Thurly\Sale\Delivery\Helper::getGroupChooseControl(
						$selectedGroupId,
						"DELIVERY_GROUP[]",
						' size="10" style="width: 300px;"'
					).
				"</td></tr></table>"
			);
			break;

		case "get_restriction_params_html":
			$className = isset($_REQUEST['className']) ? trim($_REQUEST['className']): '';
			$params = isset($_REQUEST['params']) ? $_REQUEST['params']: array();
			$deliveryId = isset($_REQUEST['deliveryId']) ? intval($_REQUEST['deliveryId']) : 0;
			$sort = isset($_REQUEST['sort']) ? intval($_REQUEST['sort']) : 100;

			/** @var \Thurly\Sale\Delivery\Restrictions\Base $className*/
			if(!$className)
				throw new \Thurly\Main\ArgumentNullException("className");

			Restrictions\Manager::getClassesList();
			$paramsStructure = $className::getParamsStructure($deliveryId);
			$params = htmlspecialcharsback($params);
			$params = $className::prepareParamsValues($params, $deliveryId);

			$paramsField = "<table width='100%'>";

			foreach($paramsStructure as $name => $param)
			{
				$paramsField .= "<tr>".
					"<td valign=\"top\" style=\"padding-right:20px;\">".(strlen($param["LABEL"]) > 0 ? $param["LABEL"].": " : "")."</td>".
					"<td>".\Thurly\Sale\Internals\Input\Manager::getEditHtml("RESTRICTION[".$name."]", $param, (isset($params[$name]) ? $params[$name] : null))."</td>".
					"</tr>";
			}

			$paramsField .= '<tr>'.
				'<td>'.Loc::getMessage("SALE_DA_SORT") .':</td>'.
				'<td><input type="text" name="SORT" value="'.$sort.'"></td>'.
				'</tr>';

			if(strlen($className::getClassDescription()) > 0)
				$paramsField .= '<tr>'.
				'<td>'.Loc::getMessage("SALE_DA_DESCR") .':</td>'.
				'<td><div class="adm-sale-delivery-restriction-descr">'.$className::getClassDescription().'</div></td>'.
				'</tr>';

			$arResult["RESTRICTION_HTML"] = $paramsField."</table>";
			break;

		case "save_restriction":
			$className = isset($_REQUEST['className']) ? trim($_REQUEST['className']): '';
			$params = isset($_REQUEST['params']) ? $_REQUEST['params'] : array();
			$sort = isset($_REQUEST['sort']) ? intval($_REQUEST['sort']) : 100;
			$deliveryId = isset($_REQUEST['deliveryId']) ? intval($_REQUEST['deliveryId']) : 0;
			$restrictionId = isset($_REQUEST['restrictionId']) ? intval($_REQUEST['restrictionId']) : 0;

			if(!$className)
				throw new \Thurly\Main\ArgumentNullException("className");

			if(!$deliveryId)
				throw new \Thurly\Main\ArgumentNullException("deliveryId");

			Restrictions\Manager::getClassesList();

			/** @var \Thurly\Sale\Delivery\Restrictions\Base $className*/

			if(get_parent_class($className) != 'Thurly\Sale\Delivery\Restrictions\Base')
				throw new \Thurly\Main\SystemException($className.' is not a child of Thurly\Sale\Delivery\Restrictions\Base'.' ('.get_parent_class($className).')');

			foreach($className::getParamsStructure() as $key => $rParams)
			{
				$errors = \Thurly\Sale\Internals\Input\Manager::getError($rParams, $params[$key]);

				if(!empty($errors))
					$arResult["ERROR"] = "Field: \"".$rParams["LABEL"]."\" ".implode("<br>", $errors)."<br>\n";
			}

			$fields = array(
				"SERVICE_ID" => $deliveryId,
				"SERVICE_TYPE" => \Thurly\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"SORT" => $sort,
				"PARAMS" => $params
			);

			$res = $className::save($fields, $restrictionId);

			if(!$res->isSuccess())
				$arResult["ERROR"] .= implode(".",$res->getErrorMessages());

			$arResult["HTML"] = getRestrictionHtml($deliveryId);

			break;

		case "delete_restriction":
			$restrictionId = isset($_REQUEST['restrictionId']) ? intval($_REQUEST['restrictionId']) : 0;
			$deliveryId = isset($_REQUEST['deliveryId']) ? intval($_REQUEST['deliveryId']) : 0;

			if(!$restrictionId)
				throw new \Thurly\Main\ArgumentNullException('restrictionId');

			$dbRes =  \Thurly\Sale\Internals\ServiceRestrictionTable::getById($restrictionId);
			Restrictions\Manager::getClassesList();
			if($fields = $dbRes->fetch())
			{
				$res = $fields["CLASS_NAME"]::delete($restrictionId, $deliveryId);

				if(!$res->isSuccess())
					$arResult["ERROR"] .= implode(".",$res->getErrorMessages());
			}
			else
			{
				$arResult["ERROR"] .= "Can't find restriction with id: ".$restrictionId;
			}

			$arResult["HTML"] = getRestrictionHtml($deliveryId);

			break;

		default:
			$arResult["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if(strlen($arResult["ERROR"]) <= 0)
		$arResult["ERROR"] = "Error! Access denied";
}

if(strlen($arResult["ERROR"]) > 0)
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));

function getRestrictionHtml($deliveryId)
{
	if(intval($deliveryId) <= 0)
		throw new \Thurly\Main\ArgumentNullException("deliveryId");

	$_REQUEST['table_id'] = 'table_delivery_restrictions';
	$_REQUEST['admin_history'] = 'Y';
	$_GET['ID'] = $deliveryId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/admin/delivery_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();

	return $restrictionsHtml;
}