<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// js/css
$APPLICATION->SetAdditionalCSS('/thurly/themes/.default/thurlyos/crm-entity-show.css');
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-paddings grid-mode pagetitle-toolbar-field-view flexible-layout crm-toolbar');
$asset = Thurly\Main\Page\Asset::getInstance();
$asset->addJs('/thurly/js/crm/common.js');

// some common langs
use Thurly\Main\Localization\Loc;
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/thurly/components/thurly/crm.invoice.menu/component.php');
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/thurly/components/thurly/crm.invoice.list/templates/.default/template.php');

// if not isset
$arResult['PATH_TO_INVOICE_EDIT'] = isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '';
$arResult['PATH_TO_INVOICE_LIST'] = isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '';
$arResult['PATH_TO_INVOICE_WIDGET'] = isset($arResult['PATH_TO_INVOICE_WIDGET']) ? $arResult['PATH_TO_INVOICE_WIDGET'] : '';
$arResult['PATH_TO_INVOICE_KANBAN'] = isset($arResult['PATH_TO_INVOICE_KANBAN']) ? $arResult['PATH_TO_INVOICE_KANBAN'] : '';

// csv and excel delegate to list
$context = \Thurly\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
if (in_array($request->get('type'), array('csv', 'excel')))
{
	LocalRedirect(str_replace(
				$arResult['PATH_TO_INVOICE_KANBAN'],
				$arResult['PATH_TO_INVOICE_LIST'],
				$APPLICATION->getCurPageParam()
			), true);
}

// main menu
$APPLICATION->IncludeComponent(
	'thurly:crm.control_panel',
	'',
	array(
		'ID' => 'INVOICE_LIST',
		'ACTIVE_ITEM_ID' => 'INVOICE',
		'PATH_TO_COMPANY_LIST' => isset($arResult['PATH_TO_COMPANY_LIST']) ? $arResult['PATH_TO_COMPANY_LIST'] : '',
		'PATH_TO_COMPANY_EDIT' => isset($arResult['PATH_TO_COMPANY_EDIT']) ? $arResult['PATH_TO_COMPANY_EDIT'] : '',
		'PATH_TO_CONTACT_LIST' => isset($arResult['PATH_TO_CONTACT_LIST']) ? $arResult['PATH_TO_CONTACT_LIST'] : '',
		'PATH_TO_CONTACT_EDIT' => isset($arResult['PATH_TO_CONTACT_EDIT']) ? $arResult['PATH_TO_CONTACT_EDIT'] : '',
		'PATH_TO_DEAL_LIST' => isset($arResult['PATH_TO_DEAL_LIST']) ? $arResult['PATH_TO_DEAL_LIST'] : '',
		'PATH_TO_DEAL_EDIT' => isset($arResult['PATH_TO_DEAL_EDIT']) ? $arResult['PATH_TO_DEAL_EDIT'] : '',
		'PATH_TO_LEAD_LIST' => isset($arResult['PATH_TO_LEAD_LIST']) ? $arResult['PATH_TO_LEAD_LIST'] : '',
		'PATH_TO_LEAD_EDIT' => isset($arResult['PATH_TO_LEAD_EDIT']) ? $arResult['PATH_TO_LEAD_EDIT'] : '',
		'PATH_TO_QUOTE_LIST' => isset($arResult['PATH_TO_QUOTE_LIST']) ? $arResult['PATH_TO_QUOTE_LIST'] : '',
		'PATH_TO_QUOTE_EDIT' => isset($arResult['PATH_TO_QUOTE_EDIT']) ? $arResult['PATH_TO_QUOTE_EDIT'] : '',
		'PATH_TO_INVOICE_LIST' => isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '',
		'PATH_TO_INVOICE_EDIT' => isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '',
		'PATH_TO_REPORT_LIST' => isset($arResult['PATH_TO_REPORT_LIST']) ? $arResult['PATH_TO_REPORT_LIST'] : '',
		'PATH_TO_DEAL_FUNNEL' => isset($arResult['PATH_TO_DEAL_FUNNEL']) ? $arResult['PATH_TO_DEAL_FUNNEL'] : '',
		'PATH_TO_EVENT_LIST' => isset($arResult['PATH_TO_EVENT_LIST']) ? $arResult['PATH_TO_EVENT_LIST'] : '',
		'PATH_TO_PRODUCT_LIST' => isset($arResult['PATH_TO_PRODUCT_LIST']) ? $arResult['PATH_TO_PRODUCT_LIST'] : ''
	),
	$component
);

// chack rights
if (!\CCrmPerms::IsAccessEnabled())
{
	return false;
}

// check accessable
if (!Thurly\Crm\Integration\ThurlyOSManager::isAccessEnabled(CCrmOwnerType::Invoice))
{
	$APPLICATION->IncludeComponent('thurly:thurlyos.business.tools.info', '', array());
}
else
{
	$entityType = \CCrmOwnerType::InvoiceName;

	// counters stub
	$isThurlyOSTemplate = SITE_TEMPLATE_ID === 'thurlyos';
	if($isThurlyOSTemplate)
	{
		$this->SetViewTarget('below_pagetitle', 0);
	}

	$APPLICATION->IncludeComponent(
		'thurly:crm.entity.counter.panel',
		'',
		array('ENTITY_TYPE_NAME' => $entityType)
	);

	if($isThurlyOSTemplate)
	{
		$this->EndViewTarget();
	}

	// menu
	$APPLICATION->IncludeComponent(
		'thurly:crm.invoice.menu',
		'',
		array(
			'PATH_TO_INVOICE_LIST' => $arResult['PATH_TO_INVOICE_LIST'],
			'PATH_TO_INVOICE_EDIT' => $arResult['PATH_TO_INVOICE_EDIT'],
			'ELEMENT_ID' => 0,
			'TYPE' => 'list',
			'DISABLE_EXPORT' => 'Y'
		),
		$component
	);

	// filter
	$APPLICATION->IncludeComponent(
		'thurly:crm.kanban.filter',
		'',
		array(
			'ENTITY_TYPE' => $entityType,
			'NAVIGATION_BAR' => array(
				'ITEMS' => array(
					array(
						//'icon' => 'kanban',
						'id' => 'kanban',
						'name' => Loc::getMessage('CRM_INVOICE_LIST_FILTER_NAV_BUTTON_KANBAN'),
						'active' => 1,
						'url' => $arResult['PATH_TO_INVOICE_KANBAN']
					),
					array(
						//'icon' => 'table',
						'id' => 'list',
						'name' => Loc::getMessage('CRM_INVOICE_LIST_FILTER_NAV_BUTTON_LIST'),
						'active' => 0,
						'url' => $arResult['PATH_TO_INVOICE_LIST']
					),
					array(
						//'icon' => 'chart',
						'id' => 'widget',
						'name' => Loc::getMessage('CRM_INVOICE_LIST_FILTER_NAV_BUTTON_WIDGET'),
						'active' => 0,
						'url' => $arResult['PATH_TO_INVOICE_WIDGET']
					)
				),
				'BINDING' => array(
					'category' => 'crm.navigation',
					'name' => 'index',
					'key' => strtolower($arResult['NAVIGATION_CONTEXT_ID'])
				)
			)
		),
		$component,
		array('HIDE_ICONS' => true)
	);

	/*
	$supervisorInv = \Thurly\Crm\Kanban\SupervisorTable::isSupervisor($entityType) ? 'N' : 'Y';
	CCrmUrlUtil::AddUrlParams(
					CComponentEngine::MakePathFromTemplate(
						$arResult['PATH_TO_INVOICE_KANBAN']
					),
					array('supervisor' => $supervisorInv, 'clear_filter' => 'Y')
				)*/

	$APPLICATION->IncludeComponent(
		'thurly:crm.kanban',
		'',
		array(
			'ENTITY_TYPE' => $entityType
		),
		$component
	);
}
