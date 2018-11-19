<?
use Thurly\Tasks\Util;
use Thurly\Tasks\Util\Type\ArrayOption;
use Thurly\Tasks\Util\Type\StructureChecker;
use Thurly\Tasks\Util\Collection;
use Thurly\Tasks\Integration\CRM;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

return new \Thurly\Tasks\UI\Component\TemplateHelper(null, $this, array(
	'RELATION' => array(
		'tasks_util_datepicker',
		'popup',
		'fx',
		'tasks_util_widget',
		'tasks_util_itemset',
		'tasks_util',
		'tasks_itemsetpicker',
		'tasks_util_query',
		'tasks_shared_form_projectplan',
		'task_calendar',
		'tasks'
	),
	'METHODS' => array(
		'detectUnitType' => function($value)
		{
			$value = intval($value); // in seconds
			$realValue = $value;

			if(!($value % 86400))
			{
				$unit = 'days';
				$value = floor($value / 86400);
			}
			elseif(!($value % 3600))
			{
				$unit = 'hours';
				$value = floor($value / 3600);
			}
			else
			{
				$unit = 'mins';
				$value = floor($value / 60);
			}

			if(!$value)
			{
				$value = '';
			}

			return array('UNIT' => $unit, 'VALUE' => $value, 'REAL_VALUE' => $realValue);
		}
	),
));