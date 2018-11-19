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

use Thurly\Tasks\Item\Converter;
use Thurly\Tasks\Item\Task\Template\CheckList;

final class ToTaskTemplateCheckList extends Converter
{
	public static function getTargetItemClass()
	{
		return CheckList::getClass();
	}

	protected function transformData(array $data, $srcInstance, $dstInstance, $result)
	{
		return array(
			'TITLE' => $data['TITLE'],
			'CHECKED' => $data['IS_COMPLETE'] == 'Y',
			'SORT' => $data['SORT_INDEX'],
		);
	}
}