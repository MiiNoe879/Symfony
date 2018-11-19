<?
use Thurly\Tasks\Util;
use Thurly\Tasks\Util\Type\ArrayOption;
use Thurly\Tasks\Util\Type\StructureChecker;
use Thurly\Tasks\Util\Collection;
use Thurly\Tasks\Integration\CRM;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// create template controller with js-dependency injections
return new \Thurly\Tasks\UI\Component\TemplateHelper(null, $this, array(
	'RELATION' => array('tasks_util', 'tasks_util_itemset'),
	'METHODS' => array(
		'templateActionGetSelector' => function($type, array $parameters)
		{
			$componentParameters = array();
			if(is_array($parameters['COMPONENT_PARAMETERS']))
			{
				$componentParameters = $parameters['COMPONENT_PARAMETERS'];
			}

			$componentParameters = array_merge(array_intersect_key($componentParameters, array_flip(array(
				// component parameter white-list place here
				'MULTIPLE',
				'NAME',
				'VALUE',
			))), array(
				// component force-to parameters place here
				'SITE_ID' => SITE_ID,
			));

			return \Thurly\Tasks\Dispatcher\PublicAction::getComponentHTML(
				"thurly:tasks.".($type == 'T' ? 'task' : 'template').".selector",
				"",
				$componentParameters
			);
		},
	),
));