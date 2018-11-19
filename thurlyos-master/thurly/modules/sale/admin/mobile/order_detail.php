<?
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_after.php');

$arParams = array(
	"ORDERS_LIST_PATH" => '/thurly/admin/mobile/sale_orders_list.php'
	);

$APPLICATION->IncludeComponent(
	'thurly:sale.mobile.order.detail',
	'.default',
	$arParams,
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_after.php');
?>