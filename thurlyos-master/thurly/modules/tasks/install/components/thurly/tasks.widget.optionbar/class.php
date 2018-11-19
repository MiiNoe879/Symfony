<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Thurly\Main\Localization\Loc;
use Thurly\Tasks\Util\Result;

Loc::loadMessages(__FILE__);

CThurlyComponent::includeComponentClass("thurly:tasks.base");

class TasksWidgetOptionBarComponent extends TasksBaseComponent
{
	protected function checkParameters()
	{
		if(!is_array($this->arParams['OPTIONS']))
		{
			$this->arParams['OPTIONS'] = array();
		}

		return $this->errors->checkNoFatals();
	}
}