<?
use Thurly\Tasks\Util;
use Thurly\Tasks\Util\Type\ArrayOption;
use Thurly\Tasks\Util\Type\StructureChecker;
use Thurly\Tasks\Util\Collection;
use Thurly\Tasks\Integration\CRM;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// create template controller with js-dependency injections
$helper = new \Thurly\Tasks\UI\Component\TemplateHelper(null, $this, array(
	'RELATION' => array('tasks_util', 'tasks_util_itemset', 'tasks_integration_socialnetwork', 'popup'),
));

return $helper;