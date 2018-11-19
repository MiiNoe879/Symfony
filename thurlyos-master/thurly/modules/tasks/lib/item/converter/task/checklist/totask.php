<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @internal
 */

namespace Thurly\Tasks\Item\Converter\Task\CheckList;

use Thurly\Tasks\Item;
use Thurly\Tasks\Item\Converter;

final class ToTask extends Converter
{
	public static function getTargetItemClass()
	{
		return Item\Task::getClass();
	}

	/**
	 * @param array $data
	 * @param Item\SubItem $srcInstance
	 * @param Item $dstInstance
	 * @param Item\Result $result
	 * @return array|null
	 */
	protected function transformData(array $data, $srcInstance, $dstInstance, $result)
	{
		$task = $srcInstance->getParent();

		// transforming the checklist item into a sub-task, just for fun
		return array(
			'TITLE' => $data['TITLE'],
			'RESPONSIBLE_ID' => $task['RESPONSIBLE_ID'],
			'CREATED_BY' => $data['CREATED_BY'],
			'PARENT_ID' => $data['TASK_ID'],
		);
	}
}