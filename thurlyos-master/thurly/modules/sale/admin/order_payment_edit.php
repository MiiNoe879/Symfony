<?
use Thurly\Main\Application;
use Thurly\Main\Page\Asset;
use Thurly\Sale\Order;
use Thurly\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/prolog.php");

$moduleId = "sale";
global $USER;
Thurly\Main\Loader::includeModule('sale');
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
CUtil::InitJSCore();
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/lib/helpers/admin/orderedit.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/lib/helpers/admin/formrecovery.php");
Asset::getInstance()->addCss('/thurly/themes/.default/sale.css');

/** @var null|\Thurly\Sale\Order $saleOrder */
$saleOrder = null;
$request = Application::getInstance()->getContext()->getRequest();
$lang = Application::getInstance()->getContext()->getLanguage();
$siteId = Application::getInstance()->getContext()->getSite();
$orderId = intval($request->get('order_id'));
$paymentId = intval($request->get('payment_id'));
$new = $paymentId <= 0;
$tableId = "order_payment_edit_info";
$backUrl = $request->get('backurl');

if($orderId <= 0 || !($saleOrder = Thurly\Sale\Order::load($orderId)))
	LocalRedirect("/thurly/admin/sale_order.php?lang=".$lang.GetFilterParams("filter_", false));

$allowedOrderStatusesView = \Thurly\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
$allowedOrderStatusesUpdate = \Thurly\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

$allowUpdate = $allowDelete = in_array($saleOrder->getField("STATUS_ID"), $allowedOrderStatusesUpdate);
$allowView = in_array($saleOrder->getField("STATUS_ID"), $allowedOrderStatusesView);


$payment = null;
$errors = array();
$fields = array();

if ($paymentId > 0)
{
	$paymentCollection = $saleOrder->getPaymentCollection();
	$payment = $paymentCollection->getItemById($paymentId);

	if (!$payment)
		LocalRedirect("/thurly/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}

$isUserResponsible = false;
$isAllowCompany = false;

if ($saleModulePermissions == 'P')
{
	$userCompanyList = \Thurly\Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

	if ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID()
		|| ($payment && $payment->getField('RESPONSIBLE_ID') == $USER->GetID()))
	{
		$isUserResponsible = true;
	}

	if ((in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList)
		|| ($payment && in_array($payment->getField('COMPANY_ID'), $userCompanyList))))
	{
		$isAllowCompany = true;
	}

	if (!$isUserResponsible && !$isAllowCompany)
	{
		LocalRedirect("/thurly/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
	}
}

if ($request->get('delete') == 'Y' && check_thurly_sessid())
{
	if(!$allowDelete)
	{
		LocalRedirect('/thurly/admin/sale_order_payment.php?lang='.$lang.GetFilterParams('filter_', false));
	}


	if ($payment)
	{
		$delResult = $payment->delete();
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
					LocalRedirect('/thurly/admin/sale_order_payment.php?lang=' . $lang . GetFilterParams('filter_', false));
			}
			else
			{
				$errors = $result->getErrorMessages();
			}
		}

	}
}

if ($request->isPost() && check_thurly_sessid() && $request->get('update'))
{
	if(!$allowUpdate)
	{
		if (isset($_POST["apply"]))
		{
			LocalRedirect("/thurly/admin/sale_order_payment_edit.php?lang=".$lang."&order_id=".$orderId."&payment_id=".$paymentId."&backurl=".urlencode($backUrl).GetFilterParams("filter_", false));
		}
		else
		{
			LocalRedirect('/thurly/admin/sale_order_payment.php?lang='.$lang.GetFilterParams('filter_', false));
		}
	}

	/**
	 * @var $result \Thurly\Main\Entity\Result;
	 */
	$result = \Thurly\Sale\Helpers\Admin\Blocks\OrderPayment::updateData($saleOrder, $request->get('PAYMENT'));
	$data = $result->getData();
	$payment = array_shift($data['PAYMENT']);

	if ($result->isSuccess())
	{
		$saveResult = $saleOrder->save();

		if ($saveResult->isSuccess())
		{
			$paymentId = $payment->getId();

			if (strlen($request->getPost("apply")) == 0)
				if ($backUrl)
					LocalRedirect($backUrl);
				else
					LocalRedirect('/thurly/admin/sale_order_payment.php?lang='.$lang.GetFilterParams('filter_', false));
			else
				LocalRedirect("/thurly/admin/sale_order_payment_edit.php?lang=".$lang."&order_id=".$orderId."&payment_id=".$paymentId."&backurl=".urlencode($backUrl).GetFilterParams("filter_", false));
		}
		else
		{
			$errors = $saveResult->getErrorMessages();
		}
	}
	else
	{
		$errors = $result->getErrorMessages();
	}
}
else
{
	if ($paymentId == 0)
	{
		$payment = $saleOrder->getPaymentCollection()->createItem();
	}

	if (!$payment)
		LocalRedirect("/thurly/admin/sale_order_payment.php?lang=".$lang.GetFilterParams("filter_", false));
}

if ((!$allowView && !$allowUpdate) || Order::isLocked($orderId))
	LocalRedirect('/thurly/admin/sale_order_payment.php?lang=' . $lang . GetFilterParams('filter_', false));


$companyParams = array(
	'select' => array('ID', 'NAME')
);

if ($paymentId)
	$title = str_replace("#ID#", $paymentId, GetMessage("EDIT_ORDER_PAYMENT"));
else
	$title = GetMessage("NEW_ORDER_PAYMENT");
$APPLICATION->SetTitle($title);


if ($paymentId > 0)
{
	global $historyEntity;

	$historyEntity = array(
		'ENTITY' => 'PAYMENT',
		'ENTITY_ID' => $paymentId
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
	"TEXT" => Loc::getMessage("SOPE_PAYMENT_TRANSITION"),
	"TITLE"=> Loc::getMessage("SOPE_PAYMENT_TRANSITION_TITLE"),
	"LINK" => "/thurly/admin/sale_order_view.php?ID=".$orderId."&lang=".$lang.GetFilterParams("filter_")
);
if (!$new)
{
	if ($allowDelete)
	{
		$aMenu[] = array(
			"TEXT" => Loc::getMessage("SOPE_PAYMENT_DELETE"),
			"TITLE" => Loc::getMessage("SOPE_PAYMENT_DELETE_TITLE"),
			"LINK" => "javascript:void(0)",
			"ONCLICK" => "if(confirm('".Loc::getMessage('SOPE_PAYMENT_DELETE_MESSAGE')."')) window.location.href='/thurly/admin/sale_order_payment_edit.php?order_id=".$orderId."&payment_id=".$paymentId."&delete=Y&".thurly_sessid_get()."&lang=".LANGUAGE_ID.GetFilterParams("filter_")."'"
		);
	}
}

$aMenu[] = array(
	"TEXT" => Loc::getMessage("SOPE_PAYMENT_LIST"),
	"TITLE"=> Loc::getMessage("SOPE_PAYMENT_LIST_TITLE"),
	"LINK" => "/thurly/admin/sale_order_payment.php?lang=".$lang.GetFilterParams("filter_")
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($errors))
	CAdminMessage::ShowMessage(implode("<br>\n", $errors));

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => GetMessage("SOP_TAB_PAYMENT"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y")
);
if ($paymentId > 0)
{
	$aTabs[] = array("DIV" => "tab_history", "TAB" => GetMessage("SOP_TAB_HISTORY"), "TITLE" => GetMessage("SOP_TAB_HISTORY"));
	$aTabs[] = array("DIV" => "tab_analysis", "TAB" => GetMessage("SOP_TAB_ANALYSIS"), "TITLE" => GetMessage("SOP_TAB_ANALYSIS"));
}

?><form method="POST" action="<?=$APPLICATION->GetCurPage()."?lang=".$lang.'&order_id='.$orderId.$urlForm.GetFilterParams("filter_", false).(($paymentId > 0) ? '&payment_id='.$paymentId : '').'&backurl='.urlencode($backUrl)?>" name="<?=$tableId?>_form" id="<?=$tableId?>_form"><?

$tabControl = new CAdminTabControlDrag($tableId, $aTabs, $moduleId, false, true);
$tabControl->Begin();
//TAB order --
$tabControl->BeginNextTab();

//prepare blocks order
$defaultBlocksOrder = array(
	"financeinfo",
	"payment",
	"buyer",
	"additional"
);

$statusOnPaid = Thurly\Main\Config\Option::get('sale', 'status_on_paid');
$statusOnAllowDelivery = Thurly\Main\Config\Option::get('sale', 'status_on_allow_delivery');
$statusOnPaid2AllowDelivery = Thurly\Main\Config\Option::get('sale', 'status_on_payed_2_allow_delivery');

if (empty($statusOnPaid) && (empty($statusOnAllowDelivery) || empty($statusOnPaid2AllowDelivery)))
	$defaultBlocksOrder[] = 'statusorder';

$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);
\Thurly\Main\Page\Asset::getInstance()->addJs("/thurly/js/sale/admin/order_ajaxer.js");
echo \Thurly\Sale\Helpers\Admin\Blocks\OrderAdditional::getScripts();
echo \Thurly\Sale\Helpers\Admin\Blocks\OrderPayment::getScripts();
echo \Thurly\Sale\Helpers\Admin\OrderEdit::getScripts($saleOrder, $tableId);
?>

<input type="hidden" name="update" value="Y">
<input type="hidden" name="lang" id="lang" value="<?=$lang;?>">
<input type="hidden" name="order_id" id="order_id" value="<?=$orderId;?>">
<?
	echo thurly_sessid_post();
	$paymentCount = 1;
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
						case "financeinfo":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderFinanceInfo::getView($saleOrder, $new);
							break;
						case "payment":
							$index = 1;
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderPayment::getEdit($payment, $index, $_POST['PAYMENT'][$index]);
							break;
						case "buyer":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderBuyer::getView($saleOrder);
							break;
						case "additional":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderAdditional::getEdit($payment, $tableId."_form", 'PAYMENT[1]');
							break;
						case "statusorder":
							echo \Thurly\Sale\Helpers\Admin\Blocks\OrderStatus::getEditSimple($USER->GetID(), 'PAYMENT[1][ORDER_STATUS_ID]', $saleOrder->getField('STATUS_ID'));
							break;
					}
					$tabControl->DraggableBlockEnd();
				}
			?>
		</div>
	</td>
</tr>

</form>
<?
//--TAB order
if ($paymentId > 0):
	//TAB history --
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td id="order-history"><?=$historyContent; ?></td>
	</tr>
<?
	//-- TAB history

	//TAB analysis --
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
				echo \Thurly\Sale\Helpers\Admin\Blocks\OrderAnalysis::getView($saleOrder, $orderBasket, true, $paymentId);
				?>
			</div>
		</td>
	</tr>
	<?

	//-- TAB analysis

endif;

$tabControl->Buttons(
	array(
		"disabled" => !$allowUpdate,
		"back_url" => $backUrl
	)
);

$tabControl->End();

?>
<?require($DOCUMENT_ROOT."/thurly/modules/main/include/epilog_admin.php");?>