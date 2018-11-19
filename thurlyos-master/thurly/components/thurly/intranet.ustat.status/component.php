<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CThurlyComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if ($this->startResultCache(600))
{
	if(defined("BX_COMP_MANAGED_CACHE"))
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->RegisterTag('intranet_ustat');
	}

	if (!CModule::IncludeModule('intranet'))
	{
		$this->abortResultCache();
		return;
	}

	$arResult['STATUS_INFO'] = \Thurly\Intranet\UStat\UStat::getStatusInformation();

	$this->IncludeComponentTemplate();
}

$APPLICATION->SetAdditionalCSS('/thurly/components/thurly/intranet.ustat/style.css');

return $arResult['STATUS_INFO'];
