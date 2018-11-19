<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @internal
 */

namespace Thurly\Tasks\Item\Converter\Task\Template\CheckList;

use Thurly\Tasks\Item\Converter;
use Thurly\Tasks\Item\Task\CheckList;

final class ToTaskCheckList extends Converter
{
	public static function getTargetItemClass()
	{
		return CheckList::getClass();
	}

	protected function transformData(array $data, $srcInstance, $dstInstance, $result)
	{
		return array(
			'TITLE' => $data['TITLE'],
			'IS_COMPLETE' => $this->checkYN($data['CHECKED']),
			'SORT_INDEX' => $data['SORT']
		);
	}

	private function checkYN($value)
	{
		return $value === 'Y' || $value === true || $value == '1' ? 'Y' : 'N';
	}
}