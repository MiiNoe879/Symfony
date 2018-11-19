<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!\Thurly\Main\Loader::includeModule("sale") || !\Thurly\Main\Loader::includeModule("catalog"))
{
	throw new \Thurly\Main\SystemException('Modules `sale` and `catalog` should be installed');
}

CThurlyComponent::includeComponentClass("thurly:catalog.bigdata.products");


class CSaleBigdataPersonalMail extends CatalogBigdataProductsComponent
{
	protected function getProductIds()
	{
		if (!empty($this->arParams['USER_ID']))
		{
			$response = \Thurly\Sale\Bigdata\Cloud::getPersonalRecommendation(
				$this->arParams['USER_ID'],
				min(1000, $this->arParams['PAGE_ELEMENT_COUNT'])
			);
		}

		if (!empty($response['items']))
		{
			return $response['items'];
		}
		else
		{
			return parent::getProductIds();
		}
	}

	public function executeComponent()
	{
		$this->arResult['ITEMS'] = $this->getProductIds();

		$this->includeComponentTemplate();
	}
}
