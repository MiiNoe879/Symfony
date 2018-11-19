<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Thurly\Tasks\Item\Task\Template\Field\ReplicateParams;
//use Thurly\Main\Localization\Loc;
//
//Loc::loadMessages(__FILE__);

CThurlyComponent::includeComponentClass("thurly:tasks.base");

class TasksWidgetReplicationComponent extends TasksBaseComponent
{
	protected function checkParameters()
	{
		$this->arParams['DATA'] = ReplicateParams::createValueStructure($this->arParams['DATA'])->get();
		$this->tryParseIntegerParameter($this->arParams['USER_ID'], 0, true);
		$this->tryParseArrayParameter($this->arParams['COMPANY_WORKTIME'], static::getCompanyWorkTime());
		return $this->errors->checkNoFatals();
	}

	protected function getData()
	{
		// week day order
		$weekStart = $this->arParams['COMPANY_WORKTIME']['WEEK_START'];

		$wdMap = array(
			0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6
		);

		// wee need mapping because of different week start
		if((string) $weekStart != '')
		{
			$wdStrMap = array(
				'MO' => 0,
				'TU' => 1,
				'WE' => 2,
				'TH' => 3,
				'FR' => 4,
				'SA' => 5,
				'SU' => 6,
			);

			$offs = $wdStrMap[$weekStart];

			$wdMap = array();
			for($k = 0; $k < 7; $k++)
			{
				$wdMap[$k] = ($k + $offs) % 7;
			}
		}

		$this->arResult['AUX_DATA']['WEEKDAY_MAP'] = $wdMap;

		if(!array_key_exists('TIMEZONE_OFFSET', $this->arParams['DATA']))
		{
			$this->arParams['DATA']['TIMEZONE_OFFSET'] = \Thurly\Tasks\Util\User::getTimeZoneOffset($this->arParams['USER_ID']);
		}
		$this->arResult['AUX_DATA']['UTC_TIME_ZONE_OFFSET'] = \Thurly\Tasks\Util::getServerTimeZoneOffset() + intval($this->arParams['DATA']['TIMEZONE_OFFSET']);
	}
}