<?
use Thurly\Main\Loader;
define("SALE_DEBUG", false); // Debug

global $DBType;

IncludeModuleLangFile(__FILE__);

$GLOBALS["SALE_FIELD_TYPES"] = array(
	"TEXT" => GetMessage("SALE_TYPE_TEXT"),
	"CHECKBOX" => GetMessage("SALE_TYPE_CHECKBOX"),
	"SELECT" => GetMessage("SALE_TYPE_SELECT"),
	"MULTISELECT" => GetMessage("SALE_TYPE_MULTISELECT"),
	"TEXTAREA" => GetMessage("SALE_TYPE_TEXTAREA"),
	"LOCATION" => GetMessage("SALE_TYPE_LOCATION"),
	"RADIO" => GetMessage("SALE_TYPE_RADIO"),
	"FILE" => GetMessage("SALE_TYPE_FILE")
);

if (!Loader::includeModule('currency'))
	return false;

// Number of processed recurring records at one time
define("SALE_PROC_REC_NUM", 3);
// Number of recurring payment attempts
define("SALE_PROC_REC_ATTEMPTS", 3);
// Time between recurring payment attempts (in seconds)
define("SALE_PROC_REC_TIME", 43200);

define("SALE_PROC_REC_FREQUENCY", 7200);
// Owner ID base name used by CSale<etnity_name>ReportHelper clases for managing the reports.
define("SALE_REPORT_OWNER_ID", 'sale');
//cache orders flag for real-time exhange with 1C
define("CACHED_b_sale_order", 3600*24);

global $SALE_TIME_PERIOD_TYPES;
$SALE_TIME_PERIOD_TYPES = array(
	"H" => GetMessage("I_PERIOD_HOUR"),
	"D" => GetMessage("I_PERIOD_DAY"),
	"W" => GetMessage("I_PERIOD_WEEK"),
	"M" => GetMessage("I_PERIOD_MONTH"),
	"Q" => GetMessage("I_PERIOD_QUART"),
	"S" => GetMessage("I_PERIOD_SEMIYEAR"),
	"Y" => GetMessage("I_PERIOD_YEAR")
);

define("SALE_VALUE_PRECISION", 4);
define("SALE_WEIGHT_PRECISION", 3);

define('BX_SALE_MENU_CATALOG_CLEAR', 'Y');

$GLOBALS["AVAILABLE_ORDER_FIELDS"] = array(
	"ID" => array("COLUMN_NAME" => "ID", "NAME" => GetMessage("SI_ORDER_ID"), "SELECT" => "ID,DATE_INSERT", "CUSTOM" => "Y", "SORT" => "ID"),
	"LID" => array("COLUMN_NAME" => GetMessage("SI_SITE"), "NAME" => GetMessage("SI_SITE"), "SELECT" => "LID", "CUSTOM" => "N", "SORT" => "LID"),
	"PERSON_TYPE" => array("COLUMN_NAME" => GetMessage("SI_PAYER_TYPE"), "NAME" => GetMessage("SI_PAYER_TYPE"), "SELECT" => "PERSON_TYPE_ID", "CUSTOM" => "Y", "SORT" => "PERSON_TYPE_ID"),
	"PAYED" => array("COLUMN_NAME" => GetMessage("SI_PAID"), "NAME" => GetMessage("SI_PAID_ORDER"), "SELECT" => "PAYED,DATE_PAYED,EMP_PAYED_ID", "CUSTOM" => "Y", "SORT" => "PAYED"),
	"PAY_VOUCHER_NUM" => array("COLUMN_NAME" => GetMessage("SI_NO_PP"), "NAME" => GetMessage("SI_NO_PP_DOC"), "SELECT" => "PAY_VOUCHER_NUM", "CUSTOM" => "N", "SORT" => "PAY_VOUCHER_NUM"),
	"PAY_VOUCHER_DATE" => array("COLUMN_NAME" => GetMessage("SI_DATE_PP"), "NAME" => GetMessage("SI_DATE_PP_DOC"), "SELECT" => "PAY_VOUCHER_DATE", "CUSTOM" => "N", "SORT" => "PAY_VOUCHER_DATE"),
	"DELIVERY_DOC_NUM" => array("COLUMN_NAME" => GetMessage("SI_DATE_PP_DELIVERY_DOC_NUM"), "NAME" => GetMessage("SI_DATE_PP_DOC_DELIVERY_DOC_NUM"), "SELECT" => "DELIVERY_DOC_NUM", "CUSTOM" => "N", "SORT" => "DELIVERY_DOC_NUM"),
	"DELIVERY_DOC_DATE" => array("COLUMN_NAME" => GetMessage("SI_DATE_PP_DELIVERY_DOC_DATE"), "NAME" => GetMessage("SI_DATE_PP_DOC_DELIVERY_DOC_DATE"), "SELECT" => "DELIVERY_DOC_DATE", "CUSTOM" => "N", "SORT" => "DELIVERY_DOC_DATE"),
	"CANCELED" => array("COLUMN_NAME" => GetMessage("SI_CANCELED"), "NAME" => GetMessage("SI_CANCELED_ORD"), "SELECT" => "CANCELED,DATE_CANCELED,EMP_CANCELED_ID", "CUSTOM" => "Y", "SORT" => "CANCELED"),
	"STATUS" => array("COLUMN_NAME" => GetMessage("SI_STATUS"), "NAME" => GetMessage("SI_STATUS_ORD"), "SELECT" => "STATUS_ID,DATE_STATUS,EMP_STATUS_ID", "CUSTOM" => "Y", "SORT" => "STATUS_ID"),
	"PRICE_DELIVERY" => array("COLUMN_NAME" => GetMessage("SI_DELIVERY"), "NAME" => GetMessage("SI_DELIVERY"), "SELECT" => "PRICE_DELIVERY,CURRENCY", "CUSTOM" => "Y", "SORT" => "PRICE_DELIVERY"),
	"ALLOW_DELIVERY" => array("COLUMN_NAME" => GetMessage("SI_ALLOW_DELIVERY"), "NAME" => GetMessage("SI_ALLOW_DELIVERY1"), "SELECT" => "ALLOW_DELIVERY,DATE_ALLOW_DELIVERY,EMP_ALLOW_DELIVERY_ID", "CUSTOM" => "Y", "SORT" => "ALLOW_DELIVERY"),
	"PRICE" => array("COLUMN_NAME" => GetMessage("SI_SUM"), "NAME" => GetMessage("SI_SUM_ORD"), "SELECT" => "PRICE,CURRENCY", "CUSTOM" => "Y", "SORT" => "PRICE"),
	"SUM_PAID" => array("COLUMN_NAME" => GetMessage("SI_SUM_PAID"), "NAME" => GetMessage("SI_SUM_PAID1"), "SELECT" => "SUM_PAID,CURRENCY", "CUSTOM" => "Y", "SORT" => "SUM_PAID"),
	"USER" => array("COLUMN_NAME" => GetMessage("SI_BUYER"), "NAME" => GetMessage("SI_BUYER"), "SELECT" => "USER_ID", "CUSTOM" => "Y", "SORT" => "USER_ID"),
	"PAY_SYSTEM" => array("COLUMN_NAME" => GetMessage("SI_PAY_SYS"), "NAME" => GetMessage("SI_PAY_SYS"), "SELECT" => "PAY_SYSTEM_ID", "CUSTOM" => "Y", "SORT" => "PAY_SYSTEM_ID"),
	"DELIVERY" => array("COLUMN_NAME" => GetMessage("SI_DELIVERY_SYS"), "NAME" => GetMessage("SI_DELIVERY_SYS"), "SELECT" => "DELIVERY_ID", "CUSTOM" => "Y", "SORT" => "DELIVERY_ID"),
	"DATE_UPDATE" => array("COLUMN_NAME" => GetMessage("SI_DATE_UPDATE"), "NAME" => GetMessage("SI_DATE_UPDATE"), "SELECT" => "DATE_UPDATE", "CUSTOM" => "N", "SORT" => "DATE_UPDATE"),
	"PS_STATUS" => array("COLUMN_NAME" => GetMessage("SI_PAYMENT_PS"), "NAME" => GetMessage("SI_PS_STATUS"), "SELECT" => "PS_STATUS,PS_RESPONSE_DATE", "CUSTOM" => "N", "SORT" => "PS_STATUS"),
	"PS_SUM" => array("COLUMN_NAME" => GetMessage("SI_PS_SUM"), "NAME" => GetMessage("SI_PS_SUM1"), "SELECT" => "PS_SUM,PS_CURRENCY", "CUSTOM" => "Y", "SORT" => "PS_SUM"),
	"TAX_VALUE" => array("COLUMN_NAME" => GetMessage("SI_TAX"), "NAME" => GetMessage("SI_TAX_SUM"), "SELECT" => "TAX_VALUE,CURRENCY", "CUSTOM" => "Y", "SORT" => "TAX_VALUE"),
	"BASKET" => array("COLUMN_NAME" => GetMessage("SI_ITEMS"), "NAME" => GetMessage("SI_ITEMS_ORD"), "SELECT" => "", "CUSTOM" => "Y", "SORT" => "")
);

CModule::AddAutoloadClasses(
	"sale",
	array(
		"sale" => "install/index.php",
		"CSaleDelivery" => $DBType."/delivery.php",
		"CSaleDeliveryHandler" => $DBType."/delivery_handler.php",
		"CSaleDeliveryHelper" => "general/delivery_helper.php",
		"CSaleDelivery2PaySystem" => "general/delivery_2_pay_system.php",
		"CSaleLocation" => $DBType."/location.php",
		"CSaleLocationGroup" => $DBType."/location_group.php",

		"CSaleBasket" => $DBType."/basket.php",
		"CSaleBasketHelper" => "general/basket_helper.php",
		"CSaleUser" => $DBType."/basket.php",

		"CSaleOrder" => $DBType."/order.php",
		"CSaleOrderPropsGroup" => $DBType."/order_props_group.php",
		"CSaleOrderPropsVariant" => $DBType."/order_props_variant.php",
		"CSaleOrderUserProps" => $DBType."/order_user_props.php",
		"CSaleOrderUserPropsValue" => $DBType."/order_user_props_value.php",
		"CSaleOrderTax" => $DBType."/order_tax.php",
		"CSaleOrderHelper" => "general/order_helper.php",

		"CSalePaySystem" => $DBType."/pay_system.php",
		"CSalePaySystemAction" => $DBType."/pay_system_action.php",
		"CSalePaySystemsHelper" => "general/pay_system_helper.php",
		"CSalePaySystemTarif" => "general/pay_system_tarif.php",

		"CSaleTax" => $DBType."/tax.php",
		"CSaleTaxRate" => $DBType."/tax_rate.php",

		"CSalePersonType" => $DBType."/person_type.php",
		"CSaleDiscount" => $DBType."/discount.php",
		"CSaleBasketDiscountConvert" => "general/step_operations.php",
		"CSaleDiscountReindex" => "general/step_operations.php",
		"CSaleDiscountConvertExt" => "general/step_operations.php",
		"CSaleUserAccount" => $DBType."/user.php",
		"CSaleUserTransact" => $DBType."/user_transact.php",
		"CSaleUserCards" => $DBType."/user_cards.php",
		"CSaleRecurring" => $DBType."/recurring.php",


		"CSaleLang" => $DBType."/settings.php",
		"CSaleGroupAccessToSite" => $DBType."/settings.php",
		"CSaleGroupAccessToFlag" => $DBType."/settings.php",

		"CSaleAuxiliary" => $DBType."/auxiliary.php",

		"CSaleAffiliate" => $DBType."/affiliate.php",
		"CSaleAffiliatePlan" => $DBType."/affiliate_plan.php",
		"CSaleAffiliatePlanSection" => $DBType."/affiliate_plan_section.php",
		"CSaleAffiliateTier" => $DBType."/affiliate_tier.php",
		"CSaleAffiliateTransact" => $DBType."/affiliate_transact.php",
		"CSaleExport" => "general/export.php", //"CSaleExport" => $DBType."/export.php",
		"ExportOneCCRM" => "general/export.php",
		"CSaleOrderLoader" => "general/order_loader.php",

		"CSaleMeasure" => "general/measurement.php",
		"CSaleProduct" => $DBType."/product.php",

		"CSaleViewedProduct" => $DBType."/product.php",

		"CSaleHelper" => "general/helper.php",
		"CSaleMobileOrderUtils" => "general/mobile_order.php",
		"CSaleMobileOrderPull" => "general/mobile_order.php",
		"CSaleMobileOrderPush" => "general/mobile_order.php",
		"CSaleMobileOrderFilter" => "general/mobile_order.php",

		"CBaseSaleReportHelper" => "general/sale_report_helper.php",
		"CSaleReportSaleOrderHelper" => "general/sale_report_helper.php",
		"CSaleReportUserHelper" => "general/sale_report_helper.php",
		"CSaleReportSaleFuserHelper" => "general/sale_report_helper.php",

		"IBXSaleProductProvider" => "general/product_provider.php",
		"CSaleStoreBarcode" => $DBType."/store_barcode.php",

		"CSaleOrderChange" => $DBType."/order_change.php",
		"CSaleOrderChangeFormat" => "general/order_change.php",

		"\\Thurly\\Sale\\Internals\\FuserTable" => "lib/internals/fuser.php",
		"\\Thurly\\Sale\\FuserTable" => "lib/internals/fuser_old.php",
		"\\Thurly\\Sale\\Fuser" => "lib/fuser.php",

		// begin lists
		'\Thurly\Sale\Internals\Input\Manager' => 'lib/internals/input.php',
		'\Thurly\Sale\Internals\Input\Base'    => 'lib/internals/input.php',
		'\Thurly\Sale\Internals\Input\File'    => 'lib/internals/input.php',
		'\Thurly\Sale\Internals\Input\StringInput'    => 'lib/internals/input.php',

		'\Thurly\Sale\Internals\SiteCurrencyTable' => 'lib/internals/sitecurrency.php',

		'CSaleStatus'                                 => 'general/status.php',
		'\Thurly\Sale\OrderStatus'                    => 'lib/status.php',
		'\Thurly\Sale\DeliveryStatus'                 => 'lib/status.php',
		'\Thurly\Sale\Internals\StatusTable'          => 'lib/internals/status.php',
		'\Thurly\Sale\Internals\StatusLangTable'      => 'lib/internals/status_lang.php',
		'\Thurly\Sale\Internals\StatusGroupTaskTable' => 'lib/internals/status_grouptask.php',
		'CSaleOrderProps'                                => 'general/order_props.php',
		'CSaleOrderPropsAdapter'                         => 'general/order_props.php',
		'CSaleOrderPropsValue'                           => 'general/order_props_values.php',
		'\Thurly\Sale\PropertyValueCollection'           => 'lib/propertyvaluecollection.php',
		'\Thurly\Sale\Internals\OrderPropsTable'         => 'lib/internals/orderprops.php',
		'\Thurly\Sale\Internals\OrderPropsGroupTable'    => 'lib/internals/orderprops_group.php',
		'\Thurly\Sale\Internals\OrderPropsValueTable'    => 'lib/internals/orderprops_value.php',
		'\Thurly\Sale\Internals\OrderPropsVariantTable'  => 'lib/internals/orderprops_variant.php',
		'\Thurly\Sale\Internals\OrderPropsRelationTable' => 'lib/internals/orderprops_relation.php',
		'\Thurly\Sale\Internals\UserPropsValueTable'     => 'lib/internals/userpropsvalue.php',
		'\Thurly\Sale\Internals\UserPropsTable'          => 'lib/internals/userprops.php',
		'\Thurly\Sale\BusinessValue'                            => 'lib/businessvalue.php',
		'\Thurly\Sale\IBusinessValueProvider'                   => 'lib/businessvalueproviderinterface.php',
		'\Thurly\Sale\Internals\BusinessValueTable'             => 'lib/internals/businessvalue.php',
		'\Thurly\Sale\Internals\BusinessValuePersonDomainTable' => 'lib/internals/businessvalue_persondomain.php',
		'\Thurly\Sale\Internals\BusinessValueCode1CTable'       => 'lib/internals/businessvalue_code_1c.php',
		'\Thurly\Sale\Internals\PaySystemActionTable' => 'lib/internals/paysystemaction.php',
		'\Thurly\Sale\Internals\PaySystemInner' => 'lib/internals/paysysteminner.php',
		'\Thurly\Sale\Internals\DeliveryPaySystemTable' => 'lib/internals/delivery_paysystem.php',
		'\Thurly\Sale\UserMessageException' => 'lib/exception.php',
		// end lists


		"Thurly\\Sale\\Internals\\DeliveryHandlerTable" => "lib/internals/deliveryhandler.php",
		"Thurly\\Sale\\DeliveryHandlerTable" => "lib/internals/deliveryhandler_old.php",

		"\\Thurly\\Sale\\Configuration" => "lib/configuration.php",
		"\\Thurly\\Sale\\Order" => "lib/order.php",
		"\\Thurly\\Sale\\PersonType" => "lib/persontype.php",

		"CSaleReportSaleGoodsHelper" => "general/sale_report_helper.php",
		"CSaleReportSaleProductHelper" => "general/sale_report_helper.php",

		"Thurly\\Sale\\ProductTable" => "lib/internals/product_old.php",
		"\\Thurly\\Sale\\ProductTable" => "lib/internals/product_old.php",
		"\\Thurly\\Sale\\Internals\\ProductTable" => "lib/internals/product.php",

		"Thurly\\Sale\\GoodsSectionTable" => "lib/internals/goodssection_old.php",
		"\\Thurly\\Sale\\GoodsSectionTable" => "lib/internals/goodssection_old.php",
		"\\Thurly\\Sale\\Internals\\GoodsSectionTable" => "lib/internals/goodssection.php",

		"Thurly\\Sale\\SectionTable" => "lib/internals/section_old.php",
		"\\Thurly\\Sale\\SectionTable" => "lib/internals/section_old.php",
		"\\Thurly\\Sale\\Internals\\SectionTable" => "lib/internals/section.php",

		"\\Thurly\\Sale\\Internals\\StoreProductTable" => "lib/internals/storeproduct.php",
		"\\Thurly\\Sale\\StoreProductTable" => "lib/internals/storeproduct_old.php",



		"\\Thurly\\Sale\\SalesZone" => "lib/saleszone.php",
		"Thurly\\Sale\\Internals\\OrderDeliveryReqTable" => "lib/internals/orderdeliveryreq.php",
		"\\Thurly\\Sale\\Internals\\OrderDeliveryReqTable" => "lib/internals/orderdeliveryreq.php",

		"Thurly\\Sale\\SenderEventHandler" => "lib/senderconnector.php",
		"Thurly\\Sale\\SenderConnectorBuyer" => "lib/senderconnector.php",

		"\\Thurly\\Sale\\UserConsent" => "lib/userconsent.php",

		"\\Thurly\\Sale\\Product2ProductTable" => "lib/internals/product2product_old.php",
		"\\Thurly\\Sale\\Internals\\Product2ProductTable" => "lib/internals/product2product.php",

		"Thurly\\Sale\\OrderProcessingTable" => "lib/internals/orderprocessing_old.php",
		"Thurly\\Sale\\Internals\\OrderProcessingTable" => "lib/internals/orderprocessing.php",

		"\\Thurly\\Sale\\OrderBase" => "lib/orderbase.php",
		"\\Thurly\\Sale\\Internals\\Entity" => "lib/internals/entity.php",
		"\\Thurly\\Sale\\Internals\\EntityCollection" => "lib/internals/entitycollection.php",
		"\\Thurly\\Sale\\Internals\\CollectionBase" => "lib/internals/collectionbase.php",
		//"\\Thurly\\Sale\\Order" => "lib/order.php",

		"\\Thurly\\Sale\\Shipment" => "lib/shipment.php",
		"\\Thurly\\Sale\\ShipmentCollection" => "lib/shipmentcollection.php",
		"\\Thurly\\Sale\\ShipmentItemCollection" => "lib/shipmentitemcollection.php",
		"\\Thurly\\Sale\\ShipmentItem" => "lib/shipmentitem.php",
		"\\Thurly\\Sale\\ShipmentItemStoreCollection" => "lib/shipmentitemstorecollection.php",
		"\\Thurly\\Sale\\ShipmentItemStore" => "lib/shipmentitemstore.php",

		"\\Thurly\\Sale\\PaymentCollectionBase" => "lib/internals/paymentcollectionbase.php",
		"\\Thurly\\Sale\\PaymentCollection" => "lib/paymentcollection.php",
		"\\Thurly\\Sale\\Payment" => "lib/payment.php",
		"\\Thurly\\Sale\\PaysystemService" => "lib/paysystemservice.php",
		"\\Thurly\\Sale\\Internals\\Fields" => "lib/internals/fields.php",
		"\\Thurly\\Sale\\Result" => "lib/result.php",
		"\\Thurly\\Sale\\ResultError" => "lib/result.php",
		"\\Thurly\\Sale\\ResultSerializable" => "lib/resultserializable.php",
		"\\Thurly\\Sale\\EventActions" => "lib/eventactions.php",

		"\\Thurly\\Sale\\Internals\\PaymentBase" => "lib/internals/paymentbase.php",
		"\\Thurly\\Sale\\BasketBase" => "lib/basketbase.php",
		"\\Thurly\\Sale\\BasketItemBase" => "lib/basketitembase.php",
		"\\Thurly\\Sale\\Basket" => "lib/basket.php",

		"\\Thurly\\Sale\\Internals\\BasketItemBase" => "lib/internals/basketitembase.php",
		"\\Thurly\\Sale\\BasketItem" => "lib/basketitem.php",
		"\\Thurly\\Sale\\BasketBundleCollection" => "lib/basketbundlecollection.php",

		"\\Thurly\\Sale\\OrderProperties" => "lib/orderprops.php",
		"\\Thurly\\Sale\\PropertyValue" => "lib/propertyvalue.php",

		"\\Thurly\\Sale\\Compatible\\Internals\\EntityCompatibility" => "lib/compatible/internals/entitycompatibility.php",
		"\\Thurly\\Sale\\Compatible\\OrderCompatibility" => "lib/compatible/ordercompatibility.php",
		"\\Thurly\\Sale\\Compatible\\BasketCompatibility" => "lib/compatible/basketcompatibility.php",
		"\\Thurly\\Sale\\Compatible\\EventCompatibility" => "lib/compatible/eventcompatibility.php",

		'\Thurly\Sale\Compatible\OrderQuery'   => 'lib/compatible/compatible.php',
		'\Thurly\Sale\Compatible\OrderQueryLocation'   => 'lib/compatible/compatible.php',
		'\Thurly\Sale\Compatible\FetchAdapter' => 'lib/compatible/compatible.php',
		'\Thurly\Sale\Compatible\Test'         => 'lib/compatible/compatible.php',

		"\\Thurly\\Sale\\OrderUserProperties" => "lib/userprops.php",

		"\\Thurly\\Sale\\BasketPropertiesCollectionBase" => "lib/basketpropertiesbase.php",
		"\\Thurly\\Sale\\BasketPropertiesCollection" => "lib/basketproperties.php",
		"\\Thurly\\Sale\\BasketPropertyItemBase" => "lib/basketpropertiesitembase.php",
		"\\Thurly\\Sale\\BasketPropertyItem" => "lib/basketpropertiesitem.php",

		"\\Thurly\\Sale\\Tax" => "lib/tax.php",

		"\\Thurly\\Sale\\Internals\\OrderTable" => "lib/internals/order.php",
		"\\Thurly\\Sale\\OrderTable" => "lib/internals/order_old.php",

		"\\Thurly\\Sale\\Internals\\BasketTable" => "lib/internals/basket.php",

		"\\Thurly\\Sale\\Internals\\ShipmentTable" => "lib/internals/shipment.php",
		"\\Thurly\\Sale\\Internals\\ShipmentItemTable" => "lib/internals/shipmentitem.php",

		"\\Thurly\\Sale\\Internals\\PaySystemServiceTable" => "lib/internals/paysystemservice.php",
		"\\Thurly\\Sale\\Internals\\PaymentTable" => "lib/internals/payment.php",

		"\\Thurly\\Sale\\Internals\\PaySystemTable" => "lib/internals/paysystem.php",
		"\\Thurly\\Sale\\PaySystemTable" => "lib/internals/paysystem_old.php",


		"\\Thurly\\Sale\\Internals\\ShipmentItemStoreTable" => "lib/internals/shipmentitemstore.php",
		"\\Thurly\\Sale\\Internals\\ShipmentExtraService" => "lib/internals/shipmentextraservice.php",

		"\\Thurly\\Sale\\Internals\\OrderUserPropertiesTable" => "lib/internals/userprops.php",

		"\\Thurly\\Sale\\Internals\\CollectableEntity" => "lib/internals/collectableentity.php",

		"\\Thurly\\Sale\\Provider" => "lib/provider.php",
		"\\Thurly\\Sale\\ProviderBase" => "lib/providerbase.php",

		'\Thurly\Sale\Internals\Catalog\Provider' => "lib/internals/catalog/provider.php",
		'\Thurly\Sale\SaleProviderBase' => "lib/saleproviderbase.php",
		'Thurly\Sale\SaleProviderBase' => "lib/saleproviderbase.php",
		'\Thurly\Sale\Internals\TransferDataProvider' => "lib/internals/transferdataprovider.php",
		'\Thurly\Sale\Internals\PoolQuantity' => "lib/internals/poolquantity.php",

		'\Thurly\Sale\Internals\ProviderCreator' => "lib/internals/providercreator.php",
		'\Thurly\Sale\Internals\ProviderBuilderBase' => "lib/internals/providerbuilderbase.php",
		'\Thurly\Sale\Internals\ProviderBuilder' => "lib/internals/providerbuilder.php",
		'\Thurly\Sale\Internals\ProviderBuilderCompatibility' => "lib/internals/providerbuildercompatibility.php",


		"\\Thurly\\Sale\\OrderHistory" => "lib/orderhistory.php",

		"\\Thurly\\Sale\\Internals\\BasketPropertyTable" => "lib/internals/basketproperties.php",
		"\\Thurly\\Sale\\Internals\\CompanyTable" => "lib/internals/company.php",
		"\\Thurly\\Sale\\Internals\\CompanyGroupTable" => "lib/internals/companygroup.php",
		"\\Thurly\\Sale\\Internals\\CompanyResponsibleGroupTable" => "lib/internals/companyresponsiblegroup.php",

		"\\Thurly\\Sale\\Internals\\PersonTypeTable" => "lib/internals/persontype.php",
		"\\Thurly\\Sale\\PersonTypeTable" => "lib/internals/persontype_old.php",
		"\\Thurly\\Sale\\Internals\\PersonTypeSiteTable" => "lib/internals/persontypesite.php",

		"\\Thurly\\Sale\\Internals\\Pool" => "lib/internals/pool.php",
		"\\Thurly\\Sale\\Internals\\UserBudgetPool" => "lib/internals/userbudgetpool.php",
		"\\Thurly\\Sale\\Internals\\EventsPool" => "lib/internals/eventspool.php",
		"\\Thurly\\Sale\\Internals\\Events" => "lib/internals/events.php",

		"\\Thurly\\Sale\\PriceMaths" => "lib/pricemaths.php",
		"\\Thurly\\Sale\\BasketComponentHelper" => "lib/basketcomponenthelper.php",
		"\\Thurly\\Sale\\Registry" => "lib/registry.php",

		"IPaymentOrder" => "lib/internals/paymentinterface.php",
		"IShipmentOrder" => "lib/internals/shipmentinterface.php",
		"IEntityMarker" => "lib/internals/entitymarkerinterface.php",

		//archive
		"\\Thurly\\Sale\\Internals\\OrderArchiveTable" => "lib/internals/orderarchive.php",
		"\\Thurly\\Sale\\Internals\\BasketArchiveTable" => "lib/internals/basketarchive.php",
		"\\Thurly\\Sale\\Internals\\OrderArchivePackedTable" => "lib/internals/orderarchivepacked.php",
		"\\Thurly\\Sale\\Internals\\BasketArchivePackedTable" => "lib/internals/basketarchivepacked.php",
		"\\Thurly\\Sale\\Archive\\Manager" => "lib/archive/manager.php",
		"\\Thurly\\Sale\\Archive\\Recovery\\Base" => "lib/archive/recovery/base.php",
		"\\Thurly\\Sale\\Archive\\Recovery\\Scheme" => "lib/archive/recovery/scheme.php",
		"\\Thurly\\Sale\\Archive\\Recovery\\Version1" => "lib/archive/recovery/version1.php",


		"Thurly\\Sale\\Tax\\RateTable" => "lib/tax/rate.php",

		////////////////////////////
		// new location 2.0
		////////////////////////////

		// data entities
		"Thurly\\Sale\\Location\\LocationTable" => "lib/location/location.php",
		"Thurly\\Sale\\Location\\Tree" => "lib/location/tree.php",
		"Thurly\\Sale\\Location\\TypeTable" => "lib/location/type.php",
		"Thurly\\Sale\\Location\\GroupTable" => "lib/location/group.php",
		"Thurly\\Sale\\Location\\ExternalTable" => "lib/location/external.php",
		"Thurly\\Sale\\Location\\ExternalServiceTable" => "lib/location/externalservice.php",

		// search
		"Thurly\\Sale\\Location\\Search\\Finder" => "lib/location/search/finder.php",
		"Thurly\\Sale\\Location\\Search\\WordTable" => "lib/location/search/word.php",
		"Thurly\\Sale\\Location\\Search\\ChainTable" => "lib/location/search/chain.php",
		"Thurly\\Sale\\Location\\Search\\SiteLinkTable" => "lib/location/search/sitelink.php",

		// lang entities
		"Thurly\\Sale\\Location\\Name\\NameEntity" => "lib/location/name/nameentity.php",
		"Thurly\\Sale\\Location\\Name\\LocationTable" => "lib/location/name/location.php",
		"Thurly\\Sale\\Location\\Name\\TypeTable" => "lib/location/name/type.php",
		"Thurly\\Sale\\Location\\Name\\GroupTable" => "lib/location/name/group.php",

		// connector from locations to other entities
		"Thurly\\Sale\\Location\\Connector" => "lib/location/connector.php",

		// link entities
		"Thurly\\Sale\\Location\\GroupLocationTable" => "lib/location/grouplocation.php",
		"Thurly\\Sale\\Location\\SiteLocationTable" => "lib/location/sitelocation.php",
		"Thurly\\Sale\\Location\\DefaultSiteTable" => "lib/location/defaultsite.php",

		// db util
		"Thurly\\Sale\\Location\\DB\\CommonHelper" => "lib/location/db/commonhelper.php",
		"Thurly\\Sale\\Location\\DB\\Helper" => "lib/location/db/".ToLower($DBType)."/helper.php",
		"Thurly\\Sale\\Location\\DB\\BlockInserter" => "lib/location/db/blockinserter.php",

		// admin logic
		"Thurly\\Sale\\Location\\Admin\\Helper" => "lib/location/admin/helper.php",
		"Thurly\\Sale\\Location\\Admin\\NameHelper" => "lib/location/admin/namehelper.php",
		"Thurly\\Sale\\Location\\Admin\\LocationHelper" => "lib/location/admin/locationhelper.php",
		"Thurly\\Sale\\Location\\Admin\\TypeHelper" => "lib/location/admin/typehelper.php",
		"Thurly\\Sale\\Location\\Admin\\GroupHelper" => "lib/location/admin/grouphelper.php",
		"Thurly\\Sale\\Location\\Admin\\DefaultSiteHelper" => "lib/location/admin/defaultsitehelper.php",
		"Thurly\\Sale\\Location\\Admin\\SiteLocationHelper" => "lib/location/admin/sitelocationhelper.php",
		"Thurly\\Sale\\Location\\Admin\\ExternalServiceHelper" => "lib/location/admin/externalservicehelper.php",
		"Thurly\\Sale\\Location\\Admin\\SearchHelper" => "lib/location/admin/searchhelper.php",


		// util
		"Thurly\\Sale\\Location\\Util\\Process" => "lib/location/util/process.php",
		"Thurly\\Sale\\Location\\Util\\CSVReader" => "lib/location/util/csvreader.php",
		"Thurly\\Sale\\Location\\Util\\Assert" => "lib/location/util/assert.php",

		// processes for step-by-step actions
		"Thurly\\Sale\\Location\\Import\\ImportProcess" => "lib/location/import/importprocess.php",
		"Thurly\\Sale\\Location\\Search\\ReindexProcess" => "lib/location/search/reindexprocess.php",

		// exceptions
		"\\Thurly\\Sale\\Location\\Tree\\NodeNotFoundException" => "lib/location/tree/exception.php",
		"\\Thurly\\Sale\\Location\\Tree\\NodeIncorrectException" => "lib/location/tree/exception.php",
		"\\Thurly\\Sale\\Location\\Exception" => "lib/location/exception.php",

		// old
		"CSaleProxyAdminResult" => "general/proxyadminresult.php", // for admin
		"CSaleProxyResult" => "general/proxyresult.php", // for public
		// other
		"Thurly\\Sale\\Location\\Migration\\CUpdaterLocationPro" => "lib/location/migration/migrate.php", // class of migrations

		////////////////////////////
		// linked entities
		////////////////////////////

		"Thurly\\Sale\\Delivery\\DeliveryLocationTable" => "lib/delivery/deliverylocation.php",
		"Thurly\\Sale\\Tax\\RateLocationTable" => "lib/tax/ratelocation.php",
		////////////////////////////

		"CSaleBasketFilter" => "general/sale_cond.php",
		"CSaleCondCtrl" => "general/sale_cond.php",
		"CSaleCondCtrlComplex" => "general/sale_cond.php",
		"CSaleCondCtrlGroup" => "general/sale_cond.php",
		"CSaleCondCtrlBasketGroup" => "general/sale_cond.php",
		"CSaleCondCtrlBasketFields" => "general/sale_cond.php",
		"CSaleCondCtrlBasketProperties" => "general/sale_cond.php",
		"CSaleCondCtrlOrderFields" => "general/sale_cond.php",
		"CSaleCondCtrlCommon" => "general/sale_cond.php",
		"CSaleCondTree" => "general/sale_cond.php",
		"CSaleCondCtrlPastOrder" => "general/sale_cond.php",
		"CSaleCondCumulativeCtrl" => "general/sale_cond.php",
		"CSaleCumulativeAction" => "general/sale_act.php",
		"CSaleActionCtrl" => "general/sale_act.php",
		"CSaleActionCtrlGroup" => "general/sale_act.php",
		"CSaleActionCtrlAction" => "general/sale_act.php",
		"CSaleDiscountActionApply" => "general/sale_act.php",
		"CSaleActionCtrlDelivery" => "general/sale_act.php",
		"CSaleActionGift" => "general/sale_act.php",
		"CSaleActionGiftCtrlGroup" => "general/sale_act.php",
		"CSaleActionCtrlBasketGroup" => "general/sale_act.php",
		"CSaleActionCtrlSubGroup" => "general/sale_act.php",
		"CSaleActionCondCtrlBasketFields" => "general/sale_act.php",
		"CSaleActionTree" => "general/sale_act.php",
		"CSaleDiscountConvert" => "general/discount_convert.php",

		"CSalePdf" => "general/pdf.php",
		"CSaleYMHandler" => "general/ym_handler.php",
		"CSaleYMLocation" => "general/ym_location.php",

		"Thurly\\Sale\\Delivery\\CalculationResult" => "lib/delivery/calculationresult.php",
		"Thurly\\Sale\\Delivery\\Services\\Table" => "lib/delivery/services/table.php",
		"Thurly\\Sale\\Delivery\\Restrictions\\Table" => "lib/delivery/restrictions/table.php",
		"Thurly\\Sale\\Delivery\\Services\\Manager" => "lib/delivery/services/manager.php",
		"Thurly\\Sale\\Delivery\\Restrictions\\Base" => "lib/delivery/restrictions/base.php",
		"Thurly\\Sale\\Delivery\\Restrictions\\Manager" => "lib/delivery/restrictions/manager.php",
		"Thurly\\Sale\\Delivery\\Services\\Base" => "lib/delivery/services/base.php",
		"Thurly\\Sale\\Delivery\\Menu" => "lib/delivery/menu.php",
		"Thurly\\Sale\\Delivery\\Services\\ObjectPool" => "lib/delivery/services/objectpool.php",

		'\Thurly\Sale\TradingPlatformTable' => 'lib/internals/tradingplatform.php',
		'\Thurly\Sale\TradingPlatform\Ebay\Policy' => 'lib/tradingplatform/ebay/policy.php',
		'\Thurly\Sale\TradingPlatform\Helper' => 'lib/tradingplatform/helper.php',
		'\Thurly\Sale\TradingPlatform\YMarket\YandexMarket' => 'lib/tradingplatform/ymarket/yandexmarket.php',
		'\Thurly\Sale\TradingPlatform\Platform' => 'lib/tradingplatform/platform.php',
		'\Thurly\Sale\TradingPlatform\Logger' => 'lib/tradingplatform/logger.php',

		'Thurly\Sale\Internals\ShipmentExtraServiceTable' => 'lib/internals/shipmentextraservice.php',
		'Thurly\Sale\Delivery\ExtraServices\Manager' => 'lib/delivery/extra_services/manager.php',
		'Thurly\Sale\Delivery\ExtraServices\Base' => 'lib/delivery/extra_services/base.php',
		'Thurly\Sale\Delivery\ExtraServices\Table' => 'lib/delivery/extra_services/table.php',
		'Thurly\Sale\Delivery\Tracking\Manager' => 'lib/delivery/tracking/manager.php',
		'Thurly\Sale\Delivery\Tracking\Table' => 'lib/delivery/tracking/table.php',
		'Thurly\Sale\Delivery\ExternalLocationMap' => 'lib/delivery/externallocationmap.php',

		'Thurly\Sale\Internals\ServiceRestrictionTable' => 'lib/internals/servicerestriction.php',
		'Thurly\Sale\Services\Base\RestrictionManager' => 'lib/services/base/restrictionmanager.php',

		'\Thurly\Sale\Compatible\DiscountCompatibility' => 'lib/compatible/discountcompatibility.php',
		'\Thurly\Sale\Discount\Context\BaseContext' => 'lib/discount/context/basecontext.php',
		'\Thurly\Sale\Discount\Context\Fuser' => 'lib/discount/context/fuser.php',
		'\Thurly\Sale\Discount\Context\User' => 'lib/discount/context/user.php',
		'\Thurly\Sale\Discount\Context\UserGroup' => 'lib/discount/context/usergroup.php',
		'\Thurly\Sale\Discount\Gift\Collection' => 'lib/discount/gift/collection.php',
		'\Thurly\Sale\Discount\Gift\Gift' => 'lib/discount/gift/gift.php',
		'\Thurly\Sale\Discount\Gift\Manager' => 'lib/discount/gift/manager.php',
		'\Thurly\Sale\Discount\Gift\RelatedDataTable' => 'lib/discount/gift/relateddata.php',
		'\Thurly\Sale\Discount\Index\IndexElementTable' => 'lib/discount/index/indexelement.php',
		'\Thurly\Sale\Discount\Index\IndexSectionTable' => 'lib/discount/index/indexsection.php',
		'\Thurly\Sale\Discount\Index\Manager' => 'lib/discount/index/manager.php',
		'\Thurly\Sale\Discount\Prediction\Manager' => 'lib/discount/prediction/manager.php',
		'\Thurly\Sale\Discount\Preset\ArrayHelper' => 'lib/discount/preset/arrayhelper.php',
		'\Thurly\Sale\Discount\Preset\BasePreset' => 'lib/discount/preset/basepreset.php',
		'\Thurly\Sale\Discount\Preset\HtmlHelper' => 'lib/discount/preset/htmlhelper.php',
		'\Thurly\Sale\Discount\Preset\Manager' => 'lib/discount/preset/manager.php',
		'\Thurly\Sale\Discount\Preset\SelectProductPreset' => 'lib/discount/preset/selectproductpreset.php',
		'\Thurly\Sale\Discount\Preset\State' => 'lib/discount/preset/state.php',
		'\Thurly\Sale\Discount\RuntimeCache\DiscountCache' => 'lib/discount/runtimecache/discountcache.php',
		'\Thurly\Sale\Discount\RuntimeCache\FuserCache' => 'lib/discount/runtimecache/fusercache.php',
		'\Thurly\Sale\Discount\Actions' => 'lib/discount/actions.php',
		'\Thurly\Sale\Discount\Analyzer' => 'lib/discount/analyzer.php',
		'\Thurly\Sale\Discount\CumulativeCalculator' => 'lib/discount/cumulativecalculator.php',
		'\Thurly\Sale\Internals\DiscountTable' => 'lib/internals/discount.php',
		'\Thurly\Sale\Internals\DiscountCouponTable' => 'lib/internals/discountcoupon.php',
		'\Thurly\Sale\Internals\DiscountEntitiesTable' => 'lib/internals/discountentities.php',
		'\Thurly\Sale\Internals\DiscountGroupTable' => 'lib/internals/discountgroup.php',
		'\Thurly\Sale\Internals\DiscountModuleTable' => 'lib/internals/discountmodule.php',
		'\Thurly\sale\Internals\OrderDiscountTable' => 'lib/internals/orderdiscount.php',
		'\Thurly\Sale\Internals\OrderDiscountDataTable' => 'lib/internals/orderdiscount.php',
		'\Thurly\sale\Internals\OrderCouponsTable' => 'lib/internals/orderdiscount.php',
		'\Thurly\sale\Internals\OrderModulesTable' => 'lib/internals/orderdiscount.php',
		'\Thurly\sale\Internals\OrderRoundTable' => 'lib/internals/orderround.php',
		'\Thurly\sale\Internals\OrderRulesTable' => 'lib/internals/orderdiscount.php',
		'\Thurly\Sale\Internals\OrderRulesDescrTable' => 'lib/internals/orderdiscount.php',
		'\Thurly\Sale\Internals\AccountNumberGenerator' => 'lib/internals/accountnumber.php',
		'\Thurly\Sale\Discount' => 'lib/discount.php',
		'\Thurly\Sale\DiscountCouponsManager' => 'lib/discountcoupon.php',
		'\Thurly\Sale\OrderDiscountManager' => 'lib/orderdiscount.php',

		'\Thurly\Sale\PaySystem\RestService' => 'lib/paysystem/restservice.php',
		'\Thurly\Sale\PaySystem\RestHandler' => 'lib/paysystem/resthandler.php',
		'\Thurly\Sale\Services\Base\RestClient' => 'lib/services/base/restclient.php',
		'\Thurly\Sale\PaySystem\Service' => 'lib/paysystem/service.php',
		'\Thurly\Sale\Internals\PaySystemRestHandlersTable' => 'lib/internals/paysystemresthandlers.php',
		'\Thurly\Sale\PaySystem\Manager' => 'lib/paysystem/manager.php',
		'\Thurly\Sale\PaySystem\BaseServiceHandler' => 'lib/paysystem/baseservicehandler.php',
		'\Thurly\Sale\PaySystem\ServiceHandler' => 'lib/paysystem/servicehandler.php',
		'\Thurly\Sale\PaySystem\IRefund' => 'lib/paysystem/irefund.php',
		'\Thurly\Sale\PaySystem\IRequested' => 'lib/paysystem/irequested.php',
		'\Thurly\Sale\PaySystem\IRefundExtended' => 'lib/paysystem/irefundextended.php',
		'\Thurly\Sale\PaySystem\Cert' => 'lib/paysystem/cert.php',
		'\Thurly\Sale\PaySystem\IPayable' => 'lib/paysystem/ipayable.php',
		'\Thurly\Sale\PaySystem\ICheckable' => 'lib/paysystem/icheckable.php',
		'\Thurly\Sale\PaySystem\IPrePayable' => 'lib/paysystem/iprepayable.php',
		'\Thurly\Sale\PaySystem\CompatibilityHandler' => 'lib/paysystem/compatibilityhandler.php',
		'\Thurly\Sale\PaySystem\IHold' => 'lib/paysystem/ihold.php',
		'\Thurly\Sale\Internals\PaymentLogTable' => 'lib/internals/paymentlog.php',
		'\Thurly\Sale\Services\PaySystem\Restrictions\Manager' => 'lib/services/paysystem/restrictions/manager.php',
		'\Thurly\Sale\Services\Base\Restriction' => 'lib/services/base/restriction.php',
		'\Thurly\Sale\Services\Base\RestrictionManager' => 'lib/services/base/restrictionmanager.php',
		'\Thurly\sale\Internals\YandexSettingsTable' => 'lib/internals/yandexsettings.php',

		'\Thurly\Sale\Services\Company\Manager' => 'lib/services/company/manager.php',
		'\Thurly\Sale\Internals\CollectionFilterIterator' => 'lib/internals/collectionfilteriterator.php',

		'\Thurly\Sale\Cashbox\Internals\Pool' => 'lib/cashbox/internals/pool.php',
		'\Thurly\Sale\Cashbox\Internals\CashboxTable' => 'lib/cashbox/internals/cashbox.php',
		'\Thurly\Sale\Cashbox\Internals\CashboxCheckTable' => 'lib/cashbox/internals/cashboxcheck.php',
		'\Thurly\Sale\Cashbox\Internals\CashboxZReportTable' => 'lib/cashbox/internals/cashboxzreport.php',
		'\Thurly\Sale\Cashbox\Internals\CashboxErrLogTable' => 'lib/cashbox/internals/cashboxerrlog.php',
		'\Thurly\Sale\Cashbox\Cashbox' => 'lib/cashbox/cashbox.php',
		'\Thurly\Sale\Cashbox\Manager' => 'lib/cashbox/manager.php',
		'\Thurly\Sale\Cashbox\IPrintImmediately' => 'lib/cashbox/iprintimmediately.php',
		'\Thurly\Sale\Cashbox\Restrictions\Manager' => 'lib/cashbox/restrictions/manager.php',

		'\Thurly\Sale\Notify' => 'lib/notify.php',
		'\Thurly\Sale\BuyerStatistic'=> '/lib/buyerstatistic.php',
		'\Thurly\Sale\Internals\BuyerStatisticTable'=> '/lib/internals/buyerstatistic.php',

		'CAdminSaleList' => 'general/admin_lib.php',
		'\Thurly\Sale\Helpers\Admin\SkuProps' => 'lib/helpers/admin/skuprops.php',
		'\Thurly\Sale\Helpers\Admin\Product' => 'lib/helpers/admin/product.php',
		'\Thurly\Sale\Helpers\Order' => 'lib/helpers/order.php',
		'\Thurly\Sale\Location\Comparator\Replacement' => 'lib/location/comparator/ru/replacement.php',
		'\Thurly\Sale\Location\Comparator\TmpTable' => 'lib/location/comparator/tmptable.php',
		'\Thurly\Sale\Location\Comparator' => 'lib/location/comparator.php',
		'\Thurly\Sale\Location\Comparator\MapResult' => 'lib/location/comparator/mapresult.php',
		'\Thurly\Sale\Location\Comparator\Mapper' => 'lib/location/comparator/mapper.php',

		'\Thurly\Sale\Exchange\EntityCollisionType' => '/lib/exchange/entitycollisiontype.php',
		'\Thurly\Sale\Exchange\EntityType' => '/lib/exchange/entitytype.php',
		'\Thurly\Sale\Exchange\OneC\ImportCollision' => '/lib/exchange/onec/importcollision.php',
		'\Thurly\Sale\Exchange\OneC\CollisionOrder' => '/lib/exchange/onec/importcollision.php',
		'\Thurly\Sale\Exchange\OneC\CollisionShipment' => '/lib/exchange/onec/importcollision.php',
		'\Thurly\Sale\Exchange\OneC\CollisionPayment' => '/lib/exchange/onec/importcollision.php',
		'\Thurly\Sale\Exchange\OneC\CollisionProfile' => '/lib/exchange/onec/importcollision.php',
		'\Thurly\Sale\Exchange\OneC\DocumentImportFactory'=> '/lib/exchange/onec/documentimportfactory.php',
		'\Thurly\Sale\Exchange\OneC\DocumentImport'=> '/lib/exchange/onec/documentimport.php',
		'\Thurly\Sale\Exchange\OneC\OrderDocument'=> '/lib/exchange/onec/orderdocument.php',
		'\Thurly\Sale\Exchange\OneC\PaymentDocument'=> '/lib/exchange/onec/paymentdocument.php',
		'\Thurly\Sale\Exchange\OneC\PaymentCashDocument'=> '/lib/exchange/onec/paymentdocument.php',
		'\Thurly\Sale\Exchange\OneC\PaymentCashLessDocument'=> '/lib/exchange/onec/paymentdocument.php',
		'\Thurly\Sale\Exchange\OneC\PaymentCardDocument'=> '/lib/exchange/onec/paymentdocument.php',
		'\Thurly\Sale\Exchange\OneC\ShipmentDocument'=> '/lib/exchange/onec/shipmentdocument.php',
		'\Thurly\Sale\Exchange\OneC\UserProfileDocument'=> '/lib/exchange/onec/userprofiledocument.php',
		'\Thurly\Sale\Exchange\OneC\Converter'=> '/lib/exchange/onec/converter.php',
		'\Thurly\Sale\Exchange\OneC\ConverterDocumentOrder' => '/lib/exchange/onec/converterdocument.php',
		'\Thurly\Sale\Exchange\OneC\ConverterDocumentShipment' => '/lib/exchange/onec/converterdocument.php',
		'\Thurly\Sale\Exchange\OneC\ConverterDocumentPayment' => '/lib/exchange/onec/converterdocument.php',
		'\Thurly\Sale\Exchange\OneC\ConverterDocumentProfile' => '/lib/exchange/onec/converterdocument.php',
		'\Thurly\Sale\Exchange\OneC\ImportCriterionBase' => '/lib/exchange/onec/importcriterion.php',
		'\Thurly\Sale\Exchange\OneC\ImportCriterionOneCCml2' => '/lib/exchange/onec/importcriterion.php',
		'\Thurly\Sale\Exchange\OneC\CriterionOrder' => '/lib/exchange/onec/importcriterion.php',
		'\Thurly\Sale\Exchange\OneC\CriterionShipment' => '/lib/exchange/onec/importcriterion.php',
		'\Thurly\Sale\Exchange\OneC\CriterionPayment' => '/lib/exchange/onec/importcriterion.php',
		'\Thurly\Sale\Exchange\OneC\CriterionProfile' => '/lib/exchange/onec/importcriterion.php',
		'\Thurly\Sale\Exchange\OneC\ImportSettings' => '/lib/exchange/onec/importsettings.php',
		'\Thurly\Sale\Exchange\Entity\EntityImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Thurly\Sale\Exchange\Entity\OrderImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Thurly\Sale\Exchange\Entity\PaymentImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Thurly\Sale\Exchange\Entity\ShipmentImportLoader'=> '/lib/exchange/entity/entityimportloader.php',
		'\Thurly\Sale\Exchange\Entity\UserProfileImportLoader'=> '/lib/exchange/entity/entityimportloader.php',		
		'\Thurly\Sale\Exchange\EntityImportFactory'=> '/lib/exchange/entity/entityimportfactory.php',
		'\Thurly\Sale\Exchange\ImportBase'=> '/lib/exchange/importbase.php',
		'\Thurly\Sale\Exchange\Entity\EntityImport'=> '/lib/exchange/entity/entityimport.php',
		'\Thurly\Sale\Exchange\Entity\OrderImport'=> '/lib/exchange/entity/orderimport.php',
		'\Thurly\Sale\Exchange\Entity\PaymentImport'=> '/lib/exchange/entity/paymentimport.php',
		'\Thurly\Sale\Exchange\Entity\PaymentCashLessImport'=> '/lib/exchange/entity/paymentimport.php',
		'\Thurly\Sale\Exchange\Entity\PaymentCardImport'=> '/lib/exchange/entity/paymentimport.php',
		'\Thurly\Sale\Exchange\Entity\PaymentCashImport'=> '/lib/exchange/entity/paymentimport.php',
		'\Thurly\Sale\Exchange\Entity\ShipmentImport'=> '/lib/exchange/entity/shipmentimport.php',
		'\Thurly\Sale\Exchange\Entity\UserImportBase'=> '/lib/exchange/entity/userimportbase.php',
		'\Thurly\Sale\Exchange\Entity\UserProfileImport'=> '/lib/exchange/entity/userprofileimport.php',
		'\Thurly\Sale\Exchange\ImportPattern'=> '/lib/exchange/importpattern.php',
		'\Thurly\Sale\Exchange\ImportOneCPackage'=> '/lib/exchange/importonecpackage.php',

		'\Thurly\Sale\Location\GeoIp' => '/lib/location/geoip.php',

		'\Thurly\Sale\Delivery\Requests\Manager' => '/lib/delivery/requests/manager.php',
		'\Thurly\Sale\Delivery\Requests\Helper' => '/lib/delivery/requests/helper.php',
		'\Thurly\Sale\Delivery\Requests\HandlerBase' => '/lib/delivery/requests/handlerbase.php',
		'\Thurly\Sale\Delivery\Requests\RequestTable' => '/lib/delivery/requests/request.php',
		'\Thurly\Sale\Delivery\Requests\ShipmentTable' => '/lib/delivery/requests/shipment.php',
		'\Thurly\Sale\Delivery\Requests\Result' => '/lib/delivery/requests/result.php',
		'\Thurly\Sale\Delivery\Requests\ResultFile' => '/lib/delivery/requests/resultfile.php',
		
		'\Thurly\Sale\Delivery\Packing\Packer' => '/lib/delivery/packing/packer.php'
	)
);

class_alias('Thurly\Sale\TradingPlatform\YMarket\YandexMarket', 'Thurly\Sale\TradingPlatform\YandexMarket');

$psConverted = \Thurly\Main\Config\Option::get('main', '~sale_paysystem_converted');
if ($psConverted == '')
{
	CAdminNotify::Add(
		array(
			"MESSAGE" => GetMessage("SALE_PAYSYSTEM_CONVERT_ERROR", array('#LANG#' => LANGUAGE_ID)),
			"TAG" => "SALE_PAYSYSTEM_CONVERT_ERROR",
			"MODULE_ID" => "sale",
			"ENABLE_CLOSE" => "Y",
			"PUBLIC_SECTION" => "N"
		)
	);
}

function GetBasketListSimple($bSkipFUserInit = true)
{
	$fUserID = (int)CSaleBasket::GetBasketUserID($bSkipFUserInit);
	if ($fUserID > 0)
		return CSaleBasket::GetList(
			array("NAME" => "ASC"),
			array("FUSER_ID" => $fUserID, "LID" => SITE_ID, "ORDER_ID" => "NULL")
		);
	else
		return False;
}

function GetBasketList($bSkipFUserInit = true)
{
	$fUserID = (int)CSaleBasket::GetBasketUserID($bSkipFUserInit);
	$arRes = array();
	if ($fUserID > 0)
	{
		$basketID = array();
		$db_res = CSaleBasket::GetList(
			array(),
			array("FUSER_ID" => $fUserID, "LID" => SITE_ID, "ORDER_ID" => false),
			false,
			false,
			array('ID', 'CALLBACK_FUNC', 'PRODUCT_PROVIDER_CLASS', 'MODULE', 'PRODUCT_ID', 'QUANTITY', 'NOTES')
		);
		while ($res = $db_res->Fetch())
		{
			$res['CALLBACK_FUNC'] = (string)$res['CALLBACK_FUNC'];
			$res['PRODUCT_PROVIDER_CLASS'] = (string)$res['PRODUCT_PROVIDER_CLASS'];
			if ($res['CALLBACK_FUNC'] != '' || $res['PRODUCT_PROVIDER_CLASS'] != '')
				CSaleBasket::UpdatePrice($res["ID"], $res["CALLBACK_FUNC"], $res["MODULE"], $res["PRODUCT_ID"], $res["QUANTITY"], 'N', $res["PRODUCT_PROVIDER_CLASS"], $res['NOTES']);
			$basketID[] = $res['ID'];
		}
		unset($res, $db_res);
		if (!empty($basketID))
		{
			$basketIterator = CSaleBasket::GetList(
				array('NAME' => 'ASC'),
				array('ID' => $basketID)
			);
			while ($basket = $basketIterator->GetNext())
				$arRes[] = $basket;
			unset($basket, $basketIterator);
		}
		unset($basketID);
	}
	return $arRes;
}

function SaleFormatCurrency($fSum, $strCurrency, $OnlyValue = false, $withoutFormat = false)
{
	if ($withoutFormat === true)
	{
		if ($fSum === '')
			return '';

		$currencyFormat = CCurrencyLang::GetFormatDescription($strCurrency);
		if ($currencyFormat === false)
		{
			$currencyFormat = CCurrencyLang::GetDefaultValues();
		}

		$intDecimals = $currencyFormat['DECIMALS'];
		if (round($fSum, $currencyFormat["DECIMALS"]) == round($fSum, 0))
			$intDecimals = 0;

		return number_format($fSum, $intDecimals, '.','');
	}

	return CCurrencyLang::CurrencyFormat($fSum, $strCurrency, !($OnlyValue === true));
}

function AutoPayOrder($ORDER_ID)
{
	$ORDER_ID = (int)$ORDER_ID;
	if ($ORDER_ID <= 0)
		return false;

	$arOrder = CSaleOrder::GetByID($ORDER_ID);
	if (!$arOrder)
		return false;
	if ($arOrder["PS_STATUS"] != "Y")
		return false;
	if ($arOrder["PAYED"] != "N")
		return false;

	if ($arOrder["CURRENCY"] == $arOrder["PS_CURRENCY"]
		&& DoubleVal($arOrder["PRICE"]) == DoubleVal($arOrder["PS_SUM"]))
	{
		if (CSaleOrder::PayOrder($arOrder["ID"], "Y", true, false))
			return true;
	}

	return false;
}

function CurrencyModuleUnInstallSale()
{
	$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SALE_INCLUDE_CURRENCY"), "SALE_DEPENDES_CURRENCY");
	return false;
}

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/ru/include.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/ru/include.php");

function PayUserAccountDeliveryOrderCallback($productID, $userID, $bPaid, $orderID, $quantity = 1)
{
	global $DB;

	$productID = IntVal($productID);
	$userID = IntVal($userID);
	$bPaid = ($bPaid ? True : False);
	$orderID = IntVal($orderID);

	if ($userID <= 0)
		return False;

	if ($orderID <= 0)
		return False;

	if (!($arOrder = CSaleOrder::GetByID($orderID)))
		return False;

	$baseLangCurrency = CSaleLang::GetLangCurrency($arOrder["LID"]);
	$arAmount = unserialize(COption::GetOptionString("sale", "pay_amount", 'a:4:{i:1;a:2:{s:6:"AMOUNT";s:2:"10";s:8:"CURRENCY";s:3:"EUR";}i:2;a:2:{s:6:"AMOUNT";s:2:"20";s:8:"CURRENCY";s:3:"EUR";}i:3;a:2:{s:6:"AMOUNT";s:2:"30";s:8:"CURRENCY";s:3:"EUR";}i:4;a:2:{s:6:"AMOUNT";s:2:"40";s:8:"CURRENCY";s:3:"EUR";}}'));
	if (!array_key_exists($productID, $arAmount))
		return False;

	$currentPrice = $arAmount[$productID]["AMOUNT"] * $quantity;
	$currentCurrency = $arAmount[$productID]["CURRENCY"];
	if ($arAmount[$productID]["CURRENCY"] != $baseLangCurrency)
	{
		$currentPrice = CCurrencyRates::ConvertCurrency($arAmount[$productID]["AMOUNT"], $arAmount[$productID]["CURRENCY"], $baseLangCurrency) * $quantity;
		$currentCurrency = $baseLangCurrency;
	}

	if (!CSaleUserAccount::UpdateAccount($userID, ($bPaid ? $currentPrice : -$currentPrice), $currentCurrency, "MANUAL", $orderID, "Payment to user account"))
		return False;

	return True;
}

/*
* Formats user name. Used everywhere in 'sale' module
*
*/
function GetFormatedUserName($userId, $bEnableId = true, $createEditLink = true)
{
	static $formattedUsersName = array();
	static $siteNameFormat = '';

	$result = (!is_array($userId)) ? '' : array();
	$newUsers = array();

	if (is_array($userId))
	{
		foreach ($userId as $id)
		{
			if (!isset($formattedUsersName[$id]))
				$newUsers[] = $id;
		}
	}
	else if(!isset($formattedUsersName[$userId]))
	{
		$newUsers[] = $userId;
	}

	if (count($newUsers) > 0)
	{
		$resUsers = \Thurly\Main\UserTable::getList(
			array(
				'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL'),
				'filter' => array('ID' => $newUsers)
			)
		);
		while ($arUser = $resUsers->Fetch())
		{
			if (strlen($siteNameFormat) == 0)
				$siteNameFormat = CSite::GetNameFormat(false);
			$formattedUsersName[$arUser['ID']] = CUser::FormatName($siteNameFormat, $arUser, true, true);
		}
	}

	if (is_array($userId))
	{
		foreach ($userId as $uId)
		{
			$formatted = '';
			if ($bEnableId)
				$formatted = '[<a href="/thurly/admin/user_edit.php?ID='.$uId.'&lang='.LANGUAGE_ID.'">'.$uId.'</a>] ';

			if (CBXFeatures::IsFeatureEnabled('SaleAccounts') && !$createEditLink)
				$formatted .= '<a href="/thurly/admin/sale_buyers_profile.php?USER_ID='.$uId.'&lang='.LANGUAGE_ID.'">';
			else
				$formatted .= '<a href="/thurly/admin/user_edit.php?ID='.$uId.'&lang='.LANGUAGE_ID.'">';
			$formatted .= $formattedUsersName[$uId];

			$formatted .= '</a>';

			$result[$uId] = $formatted;
		}
	}
	else
	{
		if ($bEnableId)
			$result .= '[<a href="/thurly/admin/user_edit.php?ID='.$userId.'&lang='.LANGUAGE_ID.'">'.$userId.'</a>] ';

		if (CBXFeatures::IsFeatureEnabled('SaleAccounts') && !$createEditLink)
			$result .= '<a href="/thurly/admin/sale_buyers_profile.php?USER_ID='.$userId.'&lang='.LANGUAGE_ID.'">';
		else
			$result .= '<a href="/thurly/admin/user_edit.php?ID='.$userId.'&lang='.LANGUAGE_ID.'">';

		$result .= $formattedUsersName[$userId];

		$result .= '</a>';
	}

	return $result;
}

/*
 * Updates basket item arrays with information about measures from catalog
 * Basically adds MEASURE_TEXT field with the measure name to each basket item array
 *
 * @param array $arBasketItems - array of basket items' arrays
 * @return array|bool
 */
function getMeasures($arBasketItems)
{
	static $measures = array();
	$newMeasure = array();
	if (Loader::includeModule('catalog'))
	{
		$arDefaultMeasure = CCatalogMeasure::getDefaultMeasure(true, true);
		$arElementId = array();
		$basketLinks = array();
		foreach ($arBasketItems as $keyBasket => $arItem)
		{
			if (isset($arItem['MEASURE_NAME']) && strlen($arItem['MEASURE_NAME']) > 0)
			{
				$measureText = $arItem['MEASURE_NAME'];
				$measureCode = intval($arItem['MEASURE_CODE']);
			}
			else
			{
				$productID = (int)$arItem["PRODUCT_ID"];
				if (!isset($basketLinks[$productID]))
					$basketLinks[$productID] = array();
				$basketLinks[$productID][] = $keyBasket;
				$arElementId[] = $productID;

				$measureText = $arDefaultMeasure['~SYMBOL_RUS'];
				$measureCode = 0;
			}

			$arBasketItems[$keyBasket]['MEASURE_TEXT'] = $measureText;
			$arBasketItems[$keyBasket]['MEASURE'] = $measureCode;
		}
		unset($productID, $keyBasket, $arItem);

		if (!empty($arElementId))
		{
			$arBasket2Measure = array();
			$dbres = CCatalogProduct::GetList(
				array(),
				array("ID" => $arElementId),
				false,
				false,
				array("ID", "MEASURE")
			);
			while ($arRes = $dbres->Fetch())
			{
				$arRes['ID'] = (int)$arRes['ID'];
				$arRes['MEASURE'] = (int)$arRes['MEASURE'];
				if ($arRes['MEASURE'] <= 0)
					continue;
				if (!isset($arBasket2Measure[$arRes['MEASURE']]))
					$arBasket2Measure[$arRes['MEASURE']] = array();
				$arBasket2Measure[$arRes['MEASURE']][] = $arRes['ID'];

				if (!isset($measures[$arRes['MEASURE']]) && !in_array($arRes['MEASURE'], $newMeasure))
					$newMeasure[] = $arRes['MEASURE'];
			}
			unset($arRes, $dbres);

			if (!empty($newMeasure))
			{
				$dbMeasure = CCatalogMeasure::GetList(
					array(),
					array("ID" => array_values($newMeasure)),
					false,
					false,
					array('ID', 'SYMBOL_RUS', 'CODE')
				);
				while ($arMeasure = $dbMeasure->Fetch())
					$measures[$arMeasure['ID']] = $arMeasure;
			}

			foreach ($arBasket2Measure as $measureId => $productIds)
			{
				if (!isset($measures[$measureId]))
					continue;
				foreach ($productIds as $productId)
				{
					if (isset($basketLinks[$productId]) && !empty($basketLinks[$productId]))
					{
						foreach ($basketLinks[$productId] as $keyBasket)
						{
							$arBasketItems[$keyBasket]['MEASURE_TEXT'] = $measures[$measureId]['SYMBOL_RUS'];
							$arBasketItems[$keyBasket]['MEASURE'] = $measures[$measureId]['ID'];
						}
					}
				}
			}
		}
	}
	return $arBasketItems;
}

/*
 * Updates basket items' arrays with information about ratio from catalog
 * Basically adds MEASURE_RATIO field with the ratio coefficient to each basket item array
 *
 * @param array $arBasketItems - array of basket items' arrays
 * @return mixed
 */
function getRatio($arBasketItems)
{
	if (Loader::includeModule('catalog'))
	{
		static $cacheRatio = array();

		$helperCacheRatio = \Thurly\Sale\BasketComponentHelper::getRatioDataCache();
		if (is_array($helperCacheRatio) && !empty($helperCacheRatio))
		{
			$cacheRatio = array_merge($cacheRatio, $helperCacheRatio);
		}

		$map = array();
		$arElementId = array();
		foreach ($arBasketItems as $key => $arItem)
		{
			if (
				(isset($arBasketItems[$key]['MEASURE_RATIO_VALUE']) && (float)$arBasketItems[$key]['MEASURE_RATIO_VALUE'] > 0)
				&& (isset($arBasketItems[$key]['MEASURE_RATIO_ID']) && (int)$arBasketItems[$key]['MEASURE_RATIO_ID'] > 0)
			)
				continue;

			$hash = md5((!empty($arItem['PRODUCT_PROVIDER_CLASS']) ? $arItem['PRODUCT_PROVIDER_CLASS']: "")."|".(!empty($arItem['MODULE']) ? $arItem['MODULE']: "")."|".$arItem["PRODUCT_ID"]);

			if (isset($cacheRatio[$hash]))
			{
				if (isset($cacheRatio[$hash]['RATIO']))
				{
					$arBasketItems[$key]["MEASURE_RATIO"] = $cacheRatio[$hash]['RATIO']; // old key
					$arBasketItems[$key]["MEASURE_RATIO_VALUE"] = $cacheRatio[$hash]["RATIO"];
				}

				if (isset($cacheRatio[$hash]['ID']))
				{
					$arBasketItems[$key]["MEASURE_RATIO_ID"] = $cacheRatio[$hash]["ID"];
				}

			}
			else
			{
				$arElementId[$arItem["PRODUCT_ID"]] = $arItem["PRODUCT_ID"];
			}

			if (!isset($map[$arItem["PRODUCT_ID"]]))
			{
				$map[$arItem["PRODUCT_ID"]] = array();
			}

			$map[$arItem["PRODUCT_ID"]][] = $key;
		}

		if (!empty($arElementId))
		{
			$dbRatio = \Thurly\Catalog\MeasureRatioTable::getList(array(
				'select' => array('*'),
				'filter' => array('@PRODUCT_ID' => $arElementId, '=IS_DEFAULT' => 'Y')
			));
			while ($arRatio = $dbRatio->fetch())
			{
				if (empty($map[$arRatio["PRODUCT_ID"]]))
					continue;

				foreach ($map[$arRatio["PRODUCT_ID"]] as $key)
				{
					$arBasketItems[$key]["MEASURE_RATIO"] = $arRatio["RATIO"]; // old key
					$arBasketItems[$key]["MEASURE_RATIO_ID"] = $arRatio["ID"];
					$arBasketItems[$key]["MEASURE_RATIO_VALUE"] = $arRatio["RATIO"];

					$itemData = $arBasketItems[$key];

					$hash = md5((!empty($itemData['PRODUCT_PROVIDER_CLASS']) ? $itemData['PRODUCT_PROVIDER_CLASS']: "")."|".(!empty($itemData['MODULE']) ? $itemData['MODULE']: "")."|".$itemData["PRODUCT_ID"]);

					$cacheRatio[$hash] = $arRatio;
				}
				unset($key);
			}
			unset($arRatio, $dbRatio);
		}
		unset($arElementId, $map);
	}
	return $arBasketItems;
}

/*
 * Creates an array of iblock properties for the elements with certain IDs
 *
 * @param array $arElementId - array of element id
 * @param array $arSelect - properties to select
 * @return array - array of properties' values in the form of array("ELEMENT_ID" => array of props)
 */
function getProductProps($arElementId, $arSelect)
{
	if (!Loader::includeModule("iblock"))
		return array();

	if (empty($arElementId))
		return array();

	$arSelect = array_filter($arSelect, 'checkProductPropCode');
	foreach (array_keys($arSelect) as $index)
	{
		if (substr($arSelect[$index], 0, 9) === 'PROPERTY_')
		{
			if (substr($arSelect[$index], -6) === '_VALUE')
				$arSelect[$index] = substr($arSelect[$index], 0, -6);
		}
	}
	unset($index);

	$arProductData = array();
	$arElementData = array();
	$res = CIBlockElement::GetList(
		array(),
		array("=ID" => array_unique($arElementId)),
		false,
		false,
		array("ID", "IBLOCK_ID")
	);
	while ($arElement = $res->Fetch())
		$arElementData[$arElement["IBLOCK_ID"]][] = $arElement["ID"]; // two getlists are used to support 1 and 2 type of iblock properties

	foreach ($arElementData as $iblockId => $arElemId) // todo: possible performance bottleneck
	{
		$res = CIBlockElement::GetList(
			array(),
			array("IBLOCK_ID" => $iblockId, "=ID" => $arElemId),
			false,
			false,
			$arSelect
		);
		while ($arElement = $res->GetNext())
		{
			$id = $arElement["ID"];
			foreach ($arElement as $key => $value)
			{
				if (!isset($arProductData[$id]))
					$arProductData[$id] = array();

				if (isset($arProductData[$id][$key])
					&& !is_array($arProductData[$id][$key])
					&& !in_array($value, explode(", ", $arProductData[$id][$key]))
				) // if we have multiple property value
				{
					$arProductData[$id][$key] .= ", ".$value;
				}
				elseif (empty($arProductData[$id][$key]))
				{
					$arProductData[$id][$key] = $value;
				}
			}
		}
	}

	return $arProductData;
}

function checkProductPropCode($selectItem)
{
	return ($selectItem !== null && $selectItem !== '' && $selectItem !== 'PROPERTY_');
}

function updateBasketOffersProps($oldProps, $newProps)
{
	if (!is_array($oldProps) || !is_array($newProps))
		return false;

	$result = array();
	if (empty($newProps))
		return $oldProps;
	if (empty($oldProps))
		return $newProps;
	foreach ($oldProps as &$oldValue)
	{
		$found = false;
		$key = false;
		$propId = (isset($oldValue['CODE']) ? (string)$oldValue['CODE'] : '').':'.$oldValue['NAME'];
		foreach ($newProps as $newKey => $newValue)
		{
			$newId = (isset($newValue['CODE']) ? (string)$newValue['CODE'] : '').':'.$newValue['NAME'];
			if ($newId == $propId)
			{
				$key = $newKey;
				$found = true;
				break;
			}
		}
		if ($found)
		{
			$oldValue['VALUE'] = $newProps[$key]['VALUE'];
			unset($newProps[$key]);
		}
		$result[] = $oldValue;
	}
	unset($oldValue);
	if (!empty($newProps))
	{
		foreach ($newProps as &$newValue)
		{
			$result[] = $newValue;
		}
		unset($newValue);
	}
	return $result;
}
