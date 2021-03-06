<?php
define('CRM_MODULE_CALENDAR_ID', 'calendar');

// Permissions -->
define('BX_CRM_PERM_NONE', '');
define('BX_CRM_PERM_SELF', 'A');
define('BX_CRM_PERM_DEPARTMENT', 'D');
define('BX_CRM_PERM_SUBDEPARTMENT', 'F');
define('BX_CRM_PERM_OPEN', 'O');
define('BX_CRM_PERM_ALL', 'X');
define('BX_CRM_PERM_CONFIG', 'C');
// <-- Permissions

// Sonet entity types -->
define('SONET_CRM_LEAD_ENTITY', 'CRMLEAD');
define('SONET_CRM_CONTACT_ENTITY', 'CRMCONTACT');
define('SONET_CRM_COMPANY_ENTITY', 'CRMCOMPANY');
define('SONET_CRM_DEAL_ENTITY', 'CRMDEAL');
define('SONET_CRM_ACTIVITY_ENTITY', 'CRMACTIVITY');
define('SONET_CRM_INVOICE_ENTITY', 'CRMINVOICE');
//<-- Sonet entity types

global $APPLICATION, $DBType, $DB;

IncludeModuleLangFile(__FILE__);

require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/crm/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/crm/classes/general/crm_usertypecrmstatus.php');
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/crm/classes/general/crm_usertypecrm.php');

CModule::AddAutoloadClasses(
	'crm',
	array(
		'CAllCrmLead' => 'classes/general/crm_lead.php',
		'CCrmLead' => 'classes/'.$DBType.'/crm_lead.php',
		'CCrmLeadWS' => 'classes/general/ws_lead.php',
		'CCRMLeadRest' => 'classes/general/rest_lead.php',
		'CAllCrmDeal' => 'classes/general/crm_deal.php',
		'CCrmDeal' => 'classes/'.$DBType.'/crm_deal.php',
		'CAllCrmCompany' => 'classes/general/crm_company.php',
		'CCrmCompany' => 'classes/'.$DBType.'/crm_company.php',
		'CAllCrmContact' => 'classes/general/crm_contact.php',
		'CCrmContact' => 'classes/'.$DBType.'/crm_contact.php',
		'CCrmContactWS' => 'classes/general/ws_contact.php',
		'CCrmPerms' => 'classes/general/crm_perms.php',
		'CCrmRole' => 'classes/general/crm_role.php',
		'CCrmFields' => 'classes/general/crm_fields.php',
		'CCrmUserType' => 'classes/general/crm_usertype.php',
		'CCrmGridOptions' => 'classes/general/crm_grids.php',
		'CCrmStatus' => 'classes/general/crm_status.php',
		'CCrmFieldMulti' => 'classes/general/crm_field_multi.php',
		'CCrmEvent' => 'classes/general/crm_event.php',
		'CCrmEMail' => 'classes/general/crm_email.php',
		'CCrmVCard' => 'classes/general/crm_vcard.php',
		'CCrmActivityTask' => 'classes/general/crm_activity_task.php',
		'CCrmActivityCalendar' => 'classes/general/crm_activity_calendar.php',
		'CUserTypeCrm' => 'classes/general/crm_usertypecrm.php',
		'CUserTypeCrmStatus' => 'classes/general/crm_usertypecrmstatus.php',
		'CCrmSearch' => 'classes/general/crm_search.php',
		'CCrmBizProc' => 'classes/general/crm_bizproc.php',
		'CCrmDocument' => 'classes/general/crm_document.php',
		'CCrmDocumentLead' => 'classes/general/crm_document_lead.php',
		'CCrmDocumentContact' => 'classes/general/crm_document_contact.php',
		'CCrmDocumentCompany' => 'classes/general/crm_document_company.php',
		'CCrmDocumentDeal' => 'classes/general/crm_document_deal.php',
		'CCrmReportHelper' => 'classes/general/crm_report_helper.php',
		'\Thurly\Crm\StatusTable' => 'lib/status.php',
		'\Thurly\Crm\EventTable' => 'lib/event.php',
		'\Thurly\Crm\EventRelationsTable' => 'lib/event.php',
		'\Thurly\Crm\DealTable' => 'lib/deal.php',
		'\Thurly\Crm\LeadTable' => 'lib/lead.php',
		'\Thurly\Crm\ContactTable' => 'lib/contact.php',
		'\Thurly\Crm\CompanyTable' => 'lib/company.php',
		'\Thurly\Crm\StatusTable' => 'lib/status.php',
		'\Thurly\Crm\DealTable' => 'lib/deal.php',
		'\Thurly\Crm\LeadTable' => 'lib/lead.php',
		'\Thurly\Crm\ContactTable' => 'lib/contact.php',
		'\Thurly\Crm\CompanyTable' => 'lib/company.php',
		'\Thurly\Crm\QuoteTable' => 'lib/quote.php',
		'CCrmExternalSale' => 'classes/general/crm_external_sale.php',
		'CCrmExternalSaleProxy' => 'classes/general/crm_external_sale_proxy.php',
		'CCrmExternalSaleImport' => 'classes/general/crm_external_sale_import.php',
		'CCrmUtils' => 'classes/general/crm_utils.php',
		'CCrmEntityHelper' => 'classes/general/entity_helper.php',
		'CAllCrmCatalog' => 'classes/general/crm_catalog.php',
		'CCrmCatalog' => 'classes/'.$DBType.'/crm_catalog.php',
		'CCrmCurrency' => 'classes/general/crm_currency.php',
		'CCrmCurrencyHelper' => 'classes/general/crm_currency_helper.php',
		'CCrmProductResult' => 'classes/general/crm_product_result.php',
		'CCrmProduct' => 'classes/general/crm_product.php',
		'CCrmProductHelper' => 'classes/general/crm_product_helper.php',
		'CAllCrmProductRow' => 'classes/general/crm_product_row.php',
		'CCrmProductRow' => 'classes/'.$DBType.'/crm_product_row.php',
		'CAllCrmInvoice' => 'classes/general/crm_invoice.php',
		'CCrmInvoice' => 'classes/'.$DBType.'/crm_invoice.php',
		'CAllCrmQuote' => 'classes/general/crm_quote.php',
		'CCrmQuote' => 'classes/'.$DBType.'/crm_quote.php',
		'CCrmOwnerType' => 'classes/general/crm_owner_type.php',
		'CCrmOwnerTypeAbbr' => 'classes/general/crm_owner_type.php',
		'Thurly\Crm\ProductTable' => 'lib/product.php',
		'Thurly\Crm\ProductRowTable' => 'lib/productrow.php',
		'Thurly\Crm\IBlockElementProxyTable' => 'lib/iblockelementproxy.php',
		'Thurly\Crm\IBlockElementGrcProxyTable' => 'lib/iblockelementproxy.php',
		'\Thurly\Crm\ProductTable' => 'lib/product.php',
		'\Thurly\Crm\ProductRowTable' => 'lib/productrow.php',
		'\Thurly\Crm\IBlockElementProxyTable' => 'lib/iblockelementproxy.php',
		'\Thurly\Crm\IBlockElementGrcProxyTable' => 'lib/iblockelementproxy.php',
		'CCrmAccountingHelper' => 'classes/general/crm_accounting_helper.php',
		'Thurly\Crm\ExternalSaleTable' => 'lib/externalsale.php',
		'\Thurly\Crm\ExternalSaleTable' => 'lib/externalsale.php',
		'CCrmExternalSaleHelper' => 'classes/general/crm_external_sale_helper.php',
		'CCrmEntityListBuilder' => 'classes/general/crm_entity_list_builder.php',
		'CCrmComponentHelper' => 'classes/general/crm_component_helper.php',
		'CCrmInstantEditorHelper' => 'classes/general/crm_component_helper.php',
		'CAllCrmActivity' => 'classes/general/crm_activity.php',
		'CCrmActivity' => 'classes/'.$DBType.'/crm_activity.php',
		'CCrmActivityType' => 'classes/general/crm_activity.php',
		'CCrmActivityStatus' => 'classes/general/crm_activity.php',
		'CCrmActivityPriority' => 'classes/general/crm_activity.php',
		'CCrmActivityNotifyType' => 'classes/general/crm_activity.php',
		'CCrmActivityStorageType' => 'classes/general/crm_activity.php',
		'CCrmContentType' => 'classes/general/crm_activity.php',
		'CCrmEnumeration' => 'classes/general/crm_enumeration.php',
		'CCrmEntitySelectorHelper' => 'classes/general/crm_entity_selector_helper.php',
		'CCrmBizProcHelper' => 'classes/general/crm_bizproc_helper.php',
		'CCrmBizProcEventType' => 'classes/general/crm_bizproc_helper.php',
		'CCrmUrlUtil' => 'classes/general/crm_url_util.php',
		'CCrmAuthorizationHelper' => 'classes/general/crm_authorization_helper.php',
		'CCrmWebDavHelper' => 'classes/general/crm_webdav_helper.php',
		'CCrmActivityDirection' => 'classes/general/crm_activity.php',
		'CCrmViewHelper' => 'classes/general/crm_view_helper.php',
		'CCrmSecurityHelper' => 'classes/general/crm_security_helper.php',
		'CCrmMailHelper' => 'classes/general/crm_mail_helper.php',
		'CCrmNotifier' => 'classes/general/crm_notifier.php',
		'CCrmNotifierSchemeType' => 'classes/general/crm_notifier.php',
		'CCrmActivityConverter' => 'classes/general/crm_activity_converter.php',
		'CCrmDateTimeHelper' => 'classes/general/datetime_helper.php',
		'CCrmEMailCodeAllocation' => 'classes/general/crm_email.php',
		'CCrmActivityCalendarSettings' => 'classes/general/crm_activity.php',
		'CCrmActivityCalendarSettings' => 'classes/general/crm_activity.php',
		'CCrmProductReportHelper' => 'classes/general/crm_report_helper.php',
		'CCrmReportManager' => 'classes/general/crm_report_helper.php',
		'CCrmCallToUrl' => 'classes/general/crm_url_util.php',
		'CCrmUrlTemplate' => 'classes/general/crm_url_util.php',
		'CCrmFileProxy' => 'classes/general/file_proxy.php',
		'CAllCrmMailTemplate' => 'classes/general/mail_template.php',
		'CCrmMailTemplate' => 'classes/'.$DBType.'/mail_template.php',
		'CCrmMailTemplateScope' =>  'classes/general/mail_template.php',
		'CCrmTemplateAdapter' =>  'classes/general/template_adapter.php',
		'CCrmTemplateMapper' =>  'classes/general/template_mapper.php',
		'CCrmTemplateManager' =>  'classes/general/template_manager.php',
		'CCrmGridContext' => 'classes/general/crm_grids.php',
		'CCrmUserCounter' => 'classes/general/user_counter.php',
		'CCrmUserCounterSettings' => 'classes/general/user_counter.php',
		'CCrmMobileHelper' => 'classes/general/mobile_helper.php',
		'CCrmStatusInvoice' => 'classes/general/crm_status_invoice.php',
		'CCrmTax' => 'classes/general/crm_tax.php',
		'CCrmVat' => 'classes/general/crm_vat.php',
		'CCrmLocations' => 'classes/general/crm_locations.php',
		'CCrmPaySystem' => 'classes/general/crm_pay_system.php',
		'CCrmRestService' => 'classes/general/restservice.php',
		'CCrmFieldInfo' => 'classes/general/field_info.php',
		'CCrmFieldInfoAttr' => 'classes/general/field_info.php',
		'CCrmActivityEmailSender' => 'classes/general/crm_activity.php',
		'CCrmProductSection' => 'classes/general/crm_product_section.php',
		'CCrmProductSectionDbResult' => 'classes/general/crm_product_section.php',
		'CCrmActivityDbResult' => 'classes/general/crm_activity.php',
		'CCrmInvoiceRestService' => 'classes/general/restservice_invoice.php',
		'CCrmInvoiceEvent' => 'classes/general/crm_invoice_event.php',
		'CCrmInvoiceEventFormat' => 'classes/general/crm_invoice_event.php',
		'CCrmLeadReportHelper' => 'classes/general/crm_report_helper.php',
		'CCrmInvoiceReportHelper' => 'classes/general/crm_report_helper.php',
		'CCrmActivityReportHelper' => 'classes/general/crm_report_helper.php',
		'CCrmLiveFeed' => 'classes/general/livefeed.php',
		'CCrmLiveFeedMessageRestProxy' => 'classes/general/restservice.php',
		'CCrmLiveFeedEntity' => 'classes/general/livefeed.php',
		'CCrmLiveFeedEvent' => 'classes/general/livefeed.php',
		'CCrmLiveFeedFilter' => 'classes/general/livefeed.php',
		'CCrmLiveFeedComponent' => 'classes/general/livefeed.php',
		'CAllCrmSonetRelation' => 'classes/general/sonet_relation.php',
		'CCrmSonetRelationType' => 'classes/general/sonet_relation.php',
		'CCrmSonetRelation' => 'classes/'.$DBType.'/sonet_relation.php',
		'CAllCrmSonetSubscription' => 'classes/general/sonet_subscription.php',
		'CCrmSonetSubscriptionType' => 'classes/general/sonet_subscription.php',
		'CCrmSonetSubscription' => 'classes/'.$DBType.'/sonet_subscription.php',
		'CCrmSipHelper' => 'classes/general/sip_helper.php',
		'CCrmSaleHelper' => 'classes/general/sale_helper.php',
		'CCrmProductFile' => 'classes/general/crm_product_file.php',
		'CCrmProductFileControl' => 'classes/general/crm_product_file.php',
		'CCrmProductPropsHelper' => 'classes/general/crm_productprops_helper.php',
		'CCrmProductSectionHelper' => 'classes/general/crm_product_section_helper.php',
		'\Thurly\Crm\Honorific' => 'lib/honorific.php',
		'\Thurly\Crm\Category\DealCategory' => 'lib/category/dealcategory.php',
		'\Thurly\Crm\Conversion\LeadConverter' => 'lib/conversion/leadconverter.php',
		'\Thurly\Crm\Conversion\EntityConversionConfigItem' => 'lib/conversion/entityconversionconfigitem.php',
		'\Thurly\Crm\Conversion\EntityConversionMapItem' => 'lib/conversion/entityconversionmapitem.php',
		'\Thurly\Crm\Conversion\EntityConversionMap' => 'lib/conversion/entityconversionmap.php',
		'\Thurly\Crm\Conversion\LeadConversionMapper' => 'lib/conversion/leadconversionmapper.php',
		'\Thurly\Crm\Conversion\LeadConversionWizard' => 'lib/conversion/leadconversionwizard.php',
		'\Thurly\Crm\Conversion\LeadConversionPhase' => 'lib/conversion/leadconversionphase.php',
		'\Thurly\Crm\Conversion\LeadConversionConfig' => 'lib/conversion/leadconversionconfig.php',
		'\Thurly\Crm\Conversion\LeadConversionScheme' => 'lib/conversion/leadconversionscheme.php',
		'\Thurly\Crm\Conversion\DealConversionConfig' => 'lib/conversion/dealconversionconfig.php',
		'\Thurly\Crm\Conversion\DealConversionScheme' => 'lib/conversion/dealconversionscheme.php',
		'\Thurly\Crm\Conversion\EntityConversionFileViewer' => 'lib/conversion/entityconversionfileviewer.php',
		'\Thurly\Crm\Conversion\Entity\EntityConversionMapTable' => 'lib/conversion/entity/entityconversionmap.php',
		'\Thurly\Crm\Conversion\ConversionWizardStep' => 'lib/conversion/conversionwizardstep.php',
		'\Thurly\Crm\Conversion\ConversionWizard' => 'lib/conversion/conversionwizard.php',
		'\Thurly\Crm\Synchronization\UserFieldSynchronizer' => 'lib/synchronization/userfieldsynchronizer.php',
		'\Thurly\Crm\Synchronization\UserFieldSynchronizationException' => 'lib/synchronization/userfieldsynchronizationexception.php',
		'\Thurly\Crm\UserField\UserFieldHistory' => 'lib/userfield/userfieldhistory.php',
		'\Thurly\Crm\UserField\FileViewer' => 'lib/userfield/fileviewer.php',
		'\Thurly\Crm\Integration\ThurlyOSManager' => 'lib/integration/thurlyosmanager.php',
		'\Thurly\Crm\Restriction\Restriction' => 'lib/restriction/restriction.php',
		'\Thurly\Crm\Restriction\RestrictionManager' => 'lib/restriction/restrictionmanager.php',
		'\Thurly\Crm\Restriction\AccessRestriction' => 'lib/restriction/accessrestriction.php',
		'\Thurly\Crm\Restriction\SqlRestriction' => 'lib/restriction/sqlrestriction.php',
		'\Thurly\Crm\Restriction\ThurlyOSAccessRestriction' => 'lib/restriction/thurlyosaccessrestriction.php',
		'\Thurly\Crm\Restriction\ThurlyOSSqlRestriction' => 'lib/restriction/thurlyossqlrestriction.php',
		'\Thurly\Crm\Restriction\ThurlyOSRestrictionInfo' => 'lib/restriction/thurlyosrestrictioninfo.php',
		'\Thurly\Crm\EntityAddress' => 'lib/entityaddress.php',
		'\Thurly\Crm\EntityRequisite' => 'lib/entityrequisite.php',
		'\Thurly\Crm\RequisiteTable' => 'lib/requisite.php',
		'\Thurly\Crm\Integration\StorageType' => 'lib/integration/storagetype.php',
		'\Thurly\Crm\Statistics\DealActivityStatisticEntry' => 'lib/statistics/dealactivitystatisticentry.php',
		'\Thurly\Crm\Statistics\LeadActivityStatisticEntry' => 'lib/statistics/leadactivitystatisticentry.php',
		'\Thurly\Crm\ActivityTable' => 'lib/activity.php',
		'\Thurly\Crm\PhaseSemantics' => 'lib/phasesemantics.php',
		'\Thurly\Crm\Activity\Planner' => 'lib/activity/planner.php',
		'\Thurly\Crm\Activity\Provider\Base' => 'lib/activity/provider/base.php',
		'\Thurly\Crm\Activity\Provider\Call' => 'lib/activity/provider/call.php',
		'\Thurly\Crm\Activity\Provider\Email' => 'lib/activity/provider/email.php',
		'\Thurly\Crm\Activity\Provider\ExternalChannel' => 'lib/activity/provider/externalchannel.php',
		'\Thurly\Crm\Activity\Provider\Livefeed' => 'lib/activity/provider/livefeed.php',
		'\Thurly\Crm\Activity\Provider\Meeting' => 'lib/activity/provider/meeting.php',
		'\Thurly\Crm\Activity\Provider\Task' => 'lib/activity/provider/task.php',
		'\Thurly\Crm\Activity\Provider\WebForm' => 'lib/activity/provider/webform.php',
		'\Thurly\Crm\Rest\CCrmExternalChannelConnector' => 'lib/rest/externalchannelconnector.php',
		'\Thurly\Crm\Rest\CCrmExternalChannelImport' => 'lib/rest/externalchannelimport.php',
		'\Thurly\Crm\Rest\CCrmExternalChannelImportPreset' => 'lib/rest/externalchannelimportpreset.php',
		'\Thurly\Crm\Rest\CCrmExternalChannelImportActivity' => 'lib/rest/externalchannel.php',
		'\Thurly\Crm\Rest\CCrmExternalChannelImportAgent' => 'lib/rest/externalchannel.php',
		'\Thurly\Crm\Rest\CCrmExternalChannelActivityType' => 'lib/rest/externalchannelactivitytype.php',
		'\Thurly\Crm\Rest\CCrmExternalChannelType' => 'lib/rest/externalchanneltype.php',
		'\Thurly\Crm\Recurring\Manager' => 'lib/recurring/manager.php',
		'\Thurly\Crm\Recurring\Calculator' => 'lib/recurring/calculator.php',
		'\Thurly\Crm\Recurring\DateType\Day' => 'lib/recurring/datetype/day.php',
		'\Thurly\Crm\Recurring\DateType\Month' => 'lib/recurring/datetype/month.php',
		'\Thurly\Crm\Recurring\DateType\Week' => 'lib/recurring/datetype/week.php',
		'\Thurly\Crm\Recurring\DateType\Year' => 'lib/recurring/datetype/year.php',
		'\Thurly\Crm\InvoiceRecurTable' => 'lib/invoicerecur.php',
	)
);

CJSCore::RegisterExt('crm_activity_planner', array(
	'js' => array('/thurly/js/crm/activity_planner.js', '/thurly/js/crm/communication_search.js'),
	'css' => '/thurly/js/crm/css/crm-activity-planner.css',
	'lang' => '/thurly/modules/crm/lang/'.LANGUAGE_ID.'/install/js/activity_planner.php',
	'rel' => array('core', 'popup', 'date', 'fx', 'socnetlogdest'),
));

CJSCore::RegisterExt('crm_recorder', array(
	'js' => array('/thurly/js/crm/recorder.js'),
	'css' => '/thurly/js/crm/css/crm-recorder.css',
	'rel' => array('webrtc_adapter', 'recorder'),
));

CJSCore::RegisterExt('crm_visit_tracker', array(
	'js' => array('/thurly/js/crm/visit.js'),
	'css' => array('/thurly/js/crm/css/visit.css', '/thurly/components/thurly/crm.activity.visit/templates/.default/style.css', '/thurly/components/thurly/crm.card.show/templates/.default/style.css'),
	'lang' => '/thurly/modules/crm/lang/'.LANGUAGE_ID.'/install/js/visit.php',
	'rel' => array('crm_recorder'),
));

CJSCore::RegisterExt('crm_form_loader', array(
	'js' => array('/thurly/js/crm/form_loader.js'),
));

CJSCore::RegisterExt('crm_import_csv', array(
		'js' => '/thurly/js/crm/import_csv.js',
		'css' => '/thurly/js/crm/css/import_csv.css',
		'lang' => '/thurly/modules/crm/lang/'.LANGUAGE_ID.'/install/js/import_csv.php',
));

\Thurly\Main\Page\Asset::getInstance()->addJsKernelInfo("crm", array("/thurly/js/crm/crm.js"));