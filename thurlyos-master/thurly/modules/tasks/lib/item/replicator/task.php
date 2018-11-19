<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 *
 *
 *
 */

namespace Thurly\Tasks\Item\Replicator;

class Task extends \Thurly\Tasks\Item\Replicator
{
	protected static function getItemClass()
	{
		return '\\Thurly\\Tasks\\Item\\Task';
	}

	protected static function getConverterClass()
	{
		return '\\Thurly\\Tasks\\Item\\Converter\\Task\\ToTask';
	}
}