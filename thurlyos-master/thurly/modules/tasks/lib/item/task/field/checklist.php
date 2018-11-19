<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 * @internal
 */

namespace Thurly\Tasks\Item\Task\Field;

class CheckList extends \Thurly\Tasks\Item\Field\Collection\Item
{
	protected static function getItemClass()
	{
		return \Thurly\Tasks\Item\Task\CheckList::getClass();
	}

	/**
	 * @param \Thurly\Tasks\Item\Task\Collection\CheckList $value
	 * @param $key
	 * @param $item
	 */
	protected function onBeforeSaveToDataBase($value, $key, $item)
	{
		parent::onBeforeSaveToDataBase($value, $key, $item);
		if($value) // actually, there should be more strict check, for isA()
		{
			$value->sealSortIndex();
		}
	}
}