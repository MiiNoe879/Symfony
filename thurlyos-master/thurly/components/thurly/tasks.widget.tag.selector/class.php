<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

//use Thurly\Main\Localization\Loc;
//
//Loc::loadMessages(__FILE__);
use Thurly\Tasks\Util\Type;

CThurlyComponent::includeComponentClass("thurly:tasks.base");

class TasksWidgetTagSelectorComponent extends TasksBaseComponent
{
	protected function checkParameters()
	{
		if(!Type::isIterable($this->arParams['DATA']))
		{
			$this->arParams['DATA'] = array();
		}

		return $this->errors->checkNoFatals();
	}
}