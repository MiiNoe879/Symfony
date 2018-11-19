<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CSaleLocationMap extends CThurlyComponent
{
	protected function checkParams($params)
	{
		if(!isset($params['EXTERNAL_LOCATION_CLASS']))
			throw new \Thurly\Main\ArgumentNullException('EXTERNAL_LOCATION_CLASS');
	}

	public function onPrepareComponentParams($params)
	{
		if(!isset($params['START_BUTTON']))
			$params['START_BUTTON'] = Loc::getMessage('SALE_LOCATION_MAP_BUTTON');

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkParams($this->arParams);
		}
		catch(\Exception $e)
		{
			ShowError($e->getMessage());
			return;
		}

		if(!CModule::IncludeModule('sale'))
		{
			ShowError("Module sale not installed!");
			return;
		}

		\Thurly\Sale\Delivery\Services\Manager::getHandlersList();

		$res = \Thurly\Sale\Location\LocationTable::getList(array(
			'runtime' => array(new \Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
			'select' => array('CNT')
		));

		if($loc = $res->fetch())
			$this->arResult['THURLY_LOCATIONS_COUNT'] = $loc['CNT'];

		/** @var \Thurly\Sale\Delivery\ExternalLocationMap $locationClass */
		$locationClass = $this->arParams['EXTERNAL_LOCATION_CLASS'];

		$res = \Thurly\Sale\Location\ExternalTable::getList(array(
			'filter' => array('=SERVICE_ID' => $locationClass::getExternalServiceId()),
			'runtime' => array(new \Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
			'select' => array('CNT')
		));

		if($loc = $res->fetch())
			$this->arResult['SERVICE_LOCATIONS_COUNT'] = $loc['CNT'];

		CJSCore::Init('core', 'ajax');
		$this->includeComponentTemplate();
	}
}