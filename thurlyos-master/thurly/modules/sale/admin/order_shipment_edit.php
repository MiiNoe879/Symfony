<?
use Thurly\Main\Application;
use Thurly\Main\Page\Asset;
use Thurly\Sale\Order;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Delivery\Requests;

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/prolog.php");

$moduleId = "sale";

global $USER, $APPLICATION;

Thurly\Main\Loader::includeModule('sale');
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
CUtil::InitJSCore();
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/lib/helpers/admin/orderedit.php");
Asset::getInstance()->addCss('/thurly/themes/.default/sale.css');

/** @var null|\Thurly\Sale\Order $saleOrder */
$saleOrder = null;
$shipment = null;
$dataArray = array();
$dataForRecovery = array();
$errors = array();
$request = Application::getInstance()->getContext()->getRequest();
$lang = Application::getInstance()->getContext()->getLanguage();
$orderId = intval($request->get('order_id'));
$shipmentId = intval($request->get('shipment_id'));
$siteId = Application::getInstance()->getContext()->getSite();
$backUrl = $request->get('backurl');
$save = $_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["apply"]) || isset($_POST["save"]));
$refresh = $_SERVER["REQUEST_METHOD"] == "POST" && !$save;

if($orderId <= 0 || !($saleOrder = Thurly\Sale\Order::load($orderId)))
	LocalRedirect("/thurly/admin/sale_order.php?lang=".$lang.GetFilterParams("filter_", false));


$allowedOrderStatusesView = \Thurly\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
$allowedOrderStatusesUpdate = \Thurly\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

$allowUpdate = in_array($saleOrder->getField("STATUS_ID"), $allowedOrderStatusesUpdate);
$allowView = in_array($saleOrder->getField("STATUS_ID"), $allowedOrderStatusesView);
$allowDelete = false;

$shipmentCollection = $saleOrder->getShipmentCollection();

if (intval($shipmentId) > 0)
{
	/** @var \Thurly\Sale\Shipment $shipment */
	$shipment = $shipmentCollection->getItemById($shipmentId);

	if(!$shipment)
		LocalRedirect("/thurly/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));

	$allowedDeliveryStatusesView = \Thurly\Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
	$allowedDeliveryStatusesUpdate = \Thurly\Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
	$allowedDeliveryStatusesDelete = \Thurly\Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('delete'));

	$allowUpdate = in_array($shipment->getField("STATUS_ID"), $allowedDeliveryStatusesUpdate);
	$allowView = in_array($shipment->getField("STATUS_ID"), $allowedDeliveryStatusesView);
	$allowDelete = in_array($shipment->getField("STATUS_ID"), $allowedDeliveryStatusesDelete);
}

$isUserResponsible = false;
$isAllowCompany = false;

if ($saleModulePermissions == 'P')
{
	$userCompanyList = \Thurly\Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

	$isUserResponsible = ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID() || $shipment->getField('RESPONSIBLE_ID') == $USER->GetID());

	$isAllowCompany = (in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList) || in_array($shipment->getField('COMPANY_ID'), $userCompanyList));

	if (!$isUserResponsible && !$isAllowCompany)
	{
		LocalRedirect("/thurly/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
	}
}

if ($request->get('delete') == 'Y' && check_thurly_sessid())
{
	if(!$allowDelete)
	{
		LocalRedirect('/thurly/admin/sale_order_shipment.php?lang='.$lang.GetFilterParams('filter_', false));
	}

	$delResult = $shipment->delete();
	if (!$delResult->isSuccess())
	{
		$errors = $delResult->getErrorMessages();
	}
	else
	{
		$result = $saleOrder->save();
		if ($result->isSuccess())
		{
			if ($backUrl)
				LocalRedirect($backUrl);
			else
				LocalRedirect('/thurly/admin/sale_order_shipment.php?lang='.$lang.GetFilterParams('filter_', false));
		}
		else
		{
			$errors = $result->getErrorMessages();
		}
	}
}

if ($request->isPost() && ($save || $refresh) && check_thurly_sessid())
{
	if(!$allowUpdate)
	{
		if (isset($_POST["apply"]))
		{
			LocalRedirect("/thurly/admin/sale_order_shipment_edit.php?lang=".$lang."&order_id=".$orderId."&shipment_id=".$shipmentId."&backurl=".urlencode($backUrl).GetFilterParams("filter_", false));
		}
		else
		{
			LocalRedirect('/thurly/admin/sale_order_shipment.php?lang='.$lang.GetFilterParams('filter_', false));
		}

	}
	$result = \Thurly\Sale\Helpers\Admin\Blocks\OrderShipment::updateData($saleOrder, $request->get('SHIPMENT'));

	$data = $result->getData();

	$shipment = null;
	if ($data['SHIPMENT'])
		$shipment = array_shift($data['SHIPMENT']);

	if ($result->isSuccess() && $save)
	{
		$saveResult = $saleOrder->save();
		if ($saveResult->isSuccess())
		{
			$shipmentId = $shipment->getId();

			if (strlen($request->getPost("apply")) == 0)
			{
				if ($backUrl)
					LocalRedirect($backUrl);

				else
					LocalRedirect("/thurly/admin/sale_order_shipment.php?lang=".$lang.GetFilterParams("filter_", false));
			}
			else
			{
				LocalRedirect("/thurly/admin/sale_order_shipment_edit.php?lang=".$lang."&order_id=".$orderId."&shipment_id=".$shipmentId."&backurl=".urlencode($backUrl).GetFilterParams("filter_", false));
			}
		}
		else
		{
			$result->addErrors($saveResult->getErrors());
			$errors = $result->getErrorMessages();
			if (empty($errors))
				$errors[] = Loc::getMessage('SOPE_SHIPMENT_ERROR_MESSAGE');
			$dataForRecovery = $request->get('SHIPMENT');
		}
	}
	else
	{
		if (!$refresh)
		{
			/** @var \Thurly\Main\Entity\EntityError $error */
			foreach ($result->getErrors() as $error)
				$errors[$error->getCode()] = $error->getMessage();

			if (empty($errors))
				$errors[] = Loc::getMessage('SOPE_SHIPMENT_ERROR_MESSAGE');
		}
		$dataForRecovery = $request->get('SHIPMENT');
	}
}
else
{
	$new = true;
	if ($shipmentId > 0 && $shipment)
	{
		$new = false;
	}

	if ($new)
	{
		$shipment = $saleOrder->getShipmentCollection()->createItem();
		\Thurly\Sale\Helpers\Admin\Blocks\OrderShipment::setShipmentByDefaultValues($shipment);
	}
}

if (!$shipment || (!$allowView && !$allowUpdate) || Order::isLocked($orderId))
	LocalRedirect("/thurly/admin/sale_order_shipment.php?lang=".$lang.GetFilterParams("filter_", false));

if ($shipmentId)
	$title = str_replace("#ID#", $shipmentId, GetMessage("EDIT_ORDER_SHIPMENT"));
else
	$title = GetMessage("NEW_ORDER_SHIPMENT");
$APPLICATION->SetTitle($title);


if ($shipmentId > 0)
{
	global $historyEntity;

	$historyEntity = array(
		'ENTITY' => 'SHIPMENT',
		'ENTITY_ID' => $shipmentId
	);
	$_GET['ID'] = $orderId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/admin/order_history.php");
	$historyContent = ob_get_contents();
	ob_end_clean();
}

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_after.php");

$aMenu = array();

	$aMenu[] = array(
		"ICON" => "btn_list",
		"TEXT" => Loc::getMessage("SOPE_SHIPMENT_TRANSITION"),
		"TITLE"=> Loc::getMessage("SOPE_SHIPMENT_TRANSITION_TITLE"),
		"LINK" => "/thurly/admin/sale_order_view.php?ID=".$orderId."&lang=".$lang.GetFilterParams("filter_")
	);

if (!$new)
{
	if($allowDelete)
	{
		$aMenu[] = array(
			"TEXT" => Loc::getMessage("SOPE_SHIPMENT_DELETE"),
			"TITLE" => Loc::getMessage("SOPE_SHIPMENT_DELETE_TITLE"),
			"LINK" => 'javascript:void(0)',
			"ONCLICK" => "if(confirm('".Loc::getMessage('SOPE_SHIPMENT_DELETE_MESSAGE')."')) window.location.href='/thurly/admin/sale_order_shipment_edit.php?order_id=".$orderId."&shipment_id=".$shipmentId."&delete=Y&".thurly_sessid_get()."&lang=".$lang.GetFilterParams("filter_")."'"
		);
	}
}

$aMenu[] = array(
	"TEXT" => Loc::getMessage("SOPE_SHIPMENT_LIST"),
	"TITLE"=> Loc::getMessage("SOPE_SHIPMENT_LIST_TITLE"),
	"LINK" => "/thurly/admin/sale_order_shipment.php?lang=".$lang.GetFilterParams("filter_")
);

if (!$new)
{

$arSysLangs = array();
$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
while ($arLang = $db_lang->Fetch())
	$arSysLangs[] = $arLang["LID"];

$arReports = array();
$dirs = array(
	$_SERVER["DOCUMENT_ROOT"]."/thurly/admin/reports/",
	$_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/reports/"

);
foreach ($dirs as $dir)
{
	if (file_exists($dir))
	{
		if ($handle = opendir($dir))
		{
			while (($file = readdir($handle)) !== false)
			{
				$file_contents = '';
				if ($file == "." || $file == ".." || $file == ".access.php" || isset($arReports[$file]))
					continue;
				if (is_file($dir.$file) && ToUpper(substr($file, -4)) == ".PHP")
				{
					$rep_title = $file;
					if ($dir == $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/reports/")
					{
						if (is_file($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/ru/reports/".$file))
							$file_contents = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/ru/reports/".$file);
					}

					if (empty($file_contents))
						$file_contents = file_get_contents($dir.$file);

					$rep_langs = "";
					$arMatches = array();
					if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
					{
						$arMatches[3] = Trim($arMatches[3]);
						if (strlen($arMatches[3]) > 0) $rep_title = $arMatches[3];
						$arMatches[2] = Trim($arMatches[2]);
						if (strlen($arMatches[2]) > 0) $rep_langs = $arMatches[2];
					}
					if (strlen($rep_langs) > 0)
					{
						$bContinue = true;
						foreach ($arSysLangs as $sysLang)
						{
							if (strpos($rep_langs, $sysLang) !== false)
							{
								$bContinue = false;
								break;
							}
						}

						if ($bContinue)
							continue;
					}

					$arReports[$file] = array(
						"TEXT" => $rep_title,
						"ONCLICK" => "window.open('/thurly/admin/sale_order_print_new.php?&ORDER_ID=".$orderId."&SHIPMENT_ID=".$shipmentId."&doc=".substr($file, 0, strlen($file)-4)."&".thurly_sessid_get()."', '_blank');"
					);
				}
			}
		}
		closedir($handle);
	}
}

	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SOPE_SHIPMENT_PRINT"),
		"TITLE" => Loc::getMessage("SOPE_SHIPMENT_PRINT_TITLE"),
		"LINK" => 'javascript:void(0)',
		"MENU" => $arReports
	);
}

if($shipmentId > 0)
{
	$deliveryId = $shipment->getDeliveryId();
	$deliveryRequestHandler = Requests\Manager::getDeliveryRequestHandlerByDeliveryId($deliveryId);

	if($deliveryRequestHandler)
	{
		$rTypesMenu = array();
		$requestId = Requests\Manager::getRequestIdByShipmentId($shipmentId);

		if($requestId > 0)
		{
			foreach(Requests\Manager::getDeliveryRequestShipmentActions($shipment) as $action => $actionName)
			{
				$rTypesMenu[] = array(
					"TEXT" => $actionName,
					"LINK" =>"javascript:BX.Sale.Delivery.Request.processRequest({action: 'actionShipmentExecute', deliveryId: ".$deliveryId.", requestAction: '".CUtil::JSEscape($action)."', requestId: ".$requestId.", shipmentIds: [".$shipmentId."], lang: '".LANGUAGE_ID."'})"
				);
			}

			if(!empty($rTypesMenu))
				$rTypesMenu[] = array("SEPARATOR" => true);

			$rTypesMenu[] = array(
				"TEXT" => Loc::getMessage('SOPE_DELIVERY_REQUEST_SHIPMENT_UPDATE'),
				"LINK" => "javascript:BX.Sale.Delivery.Request.processRequest({action: 'updateShipmentsFromDeliveryRequest', shipmentIds: [".$shipmentId."]}, true)"
			);

			$rTypesMenu[] = array(
				"TEXT" => Loc::getMessage('SOPE_DELIVERY_REQUEST_SHIPMENT_DELETE'),
				"LINK" => "javascript:BX.Sale.Delivery.Request.processRequest({action: 'deleteShipmentsFromDeliveryRequest', shipmentIds: [".$shipmentId."]}, true)"
			);
		}
		else
		{
			$rTypesMenu[] = array(
				"TEXT" => Loc::getMessage('SOPE_DELIVERY_REQUEST_CREATE'),
				"LINK" => "/thurly/admin/sale_delivery_request.php?lang=".LANGUAGE_ID."&ACTION=CREATE_DELIVERY_REQUEST&SHIPMENT_IDS[]=".$shipmentId."&BACK_URL=".urlencode($APPLICATION->GetCurPageParam())
			);

			$rTypesMenu[] = array(
				"TEXT" => Loc::getMessage('SOPE_DELIVERY_REQUEST_ADD'),
				"LINK" => "/thurly/admin/sale_delivery_request.php?lang=".LANGUAGE_ID."&ACTION=ADD_SHIPMENTS_TO_REQUEST&SHIPMENT_IDS[]=".$shipmentId."&BACK_URL=".urlencode($APPLICATION->GetCurPageParam())
			);
		}

		$aMenu[] = array(
			"TEXT" => Loc::getMessage('SOPE_DELIVERY_REQUEST'),
			"TITLE" => Loc::getMessage('SOPE_DELIVERY_REQUEST_TITLE'),
			"LINK" => 'javascript:void(0)',
			"MENU" => $rTypesMenu
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

// Problem block
?><div id="sale-adm-order-problem-block"><?
if($shipmentId > 0 && $shipment->getField("MARKED") == "Y")
{
	echo \Thurly\Sale\Helpers\Admin\Blocks\OrderMarker::getViewForEntity($saleOrder->getId(), $shipmentId);
}
?></div><?

if(!empty($errors))
	CAdminMessage::ShowMessage(implode("<br>\n", $errors));

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => GetMessage("SOP_TAB_SHIPMENT"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y")
);

if ($shipmentId > 0)
{
	$aTabs[] = array("DIV" => "tab_history", "TAB" => GetMessage("SOP_TAB_HISTORY"), "TITLE" => GetMessage("SOP_TAB_HISTORY"));
	$aTabs[] = array("DIV" => "tab_analysis", "TAB" => GetMessage("SOP_TAB_ANALYSIS"), "TITLE" => GetMessage("SOP_TAB_ANALYSIS"));
}

$formId = "order_shipment_edit_info";
?><form method="POST" action="<?=$APPLICATION->GetCurPage()."?lang=".$lang.'&order_id='.$orderId.$urlForm.GetFilterParams("filter_", false).(($shipmentId > 0) ? '&shipment_id='.$shipmentId : '').'&backurl='.urlencode($backUrl)?>" name="<?=$formId?>_form" id="<?=$formId?>_form"><?
$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);
$tabControl->Begin();

//TAB order --
$tabControl->BeginNextTab();

//prepare blocks order
$defaultBlocksOrder = array(
	"goodsList",
	"shipmentStatus",
	"shipment",
	"buyer",
	"additional"
);
$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);
$shipmentOrderBasket = new \Thurly\Sale\Helpers\Admin\Blocks\OrderBasketShipment($shipment, "BX.Sale.Admin.ShipmentBasketObj", "sale_shipment_basket");
?>

<input type="hidden" name="lang" id="lang" value="<?=$lang;?>">
<input type="hidden" id="order_id" name="order_id" value="<?=$orderId?>">
<input type="hidden" id="site_id" name="site_id" value="<?=$siteId;?>">
<?=thurly_sessid_post();?>
<?
\Thurly\Main\Page\Asset::getInstance()->addJs("/thurly/js/sale/admin/order_ajaxer.js");
\Thurly\Sale\Delivery\Requests\Manager::initJs();

echo \Thurly\Sale\Helpers\Admin\Blocks\OrderAdditional::getScripts();
echo \Thurly\Sale\Helpers\Admin\OrderEdit::getScripts($saleOrder, $formId);
echo \Thurly\Sale\Helpers\Admin\Blocks\OrderShipment::getScripts();
echo $shipmentOrderBasket->getScripts($dataForRecovery);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/general/admin_tool.php");
?>
<tr>
	<td>
		<div style="position: relative; vertical-align: top">
			<?$tabControl->DraggableBlocksStart();?>
			<?
				foreach ($blocksOrder as $blockCode)
				{
					$tabControl->DraggableBlockBegin(GetMessage("SALE_BLOCK_TITLE_".toUpper($blockCode)), $blockCode);
					switch ($blockCode)
					{
						case "goodsList":
							echo $shipmentOrderBasket->getEdit();
							echo '<div style="display: none;">'.$shipmentOrderBasket->settingsDialog->getHtml().'</div>';
							break;
						case "shipmentStatus":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderShipmentStatus::getEdit($shipment);
							break;
						case "shipment":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderShipment::getEdit($shipment, 0, 'edit', $dataForRecovery[1]);
							break;
						case "buyer":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderBuyer::getView($saleOrder);
							break;
						case "additional":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderAdditional::getEdit($shipment, $formId.'_form', 'SHIPMENT[1]');
							break;
					}
					$tabControl->DraggableBlockEnd();
				}
			?>
		</div>
	</td>
</tr>

<?

//--TAB order
$tabControl->EndTab();
?>
</form>
<?
if ($shipmentId > 0):
	//TAB history --
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td id="order-history"><?= $historyContent; ?></td>
	</tr>
<?
	//-- TAB history
	$tabControl->EndTab();

	$tabControl->BeginNextTab();

	?>
	<tr>
		<td>
			<div style="position:relative; vertical-align:top">
				<?
				$orderBasket = new \Thurly\Sale\Helpers\Admin\Blocks\OrderBasket(
					$saleOrder,
					"BX.Sale.Admin.OrderBasketObj",
					"sale_order_basket",
					true,
					\Thurly\Sale\Helpers\Admin\Blocks\OrderBasket::VIEW_MODE
				);
				echo \Thurly\Sale\Helpers\Admin\Blocks\OrderAnalysis::getView($saleOrder, $orderBasket, false, $shipmentId);
				?>
			</div>
		</td>
	</tr>
	<?

	//-- TAB analysis
	$tabControl->EndTab();
endif;
$tabControl->Buttons(
	array(
		"disabled" => !$allowUpdate,
		"back_url" => $backUrl
	)
);

$tabControl->End();
?>
<div style="display: none;">
	<?=$shipmentOrderBasket->getSettingsDialogContent();?>
</div>
<?require($DOCUMENT_ROOT."/thurly/modules/main/include/epilog_admin.php");?>