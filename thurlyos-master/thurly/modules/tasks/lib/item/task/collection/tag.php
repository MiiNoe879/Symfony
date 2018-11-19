<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task\Collection;

use Thurly\Tasks\Util\Type;
use Thurly\Tasks\Item;

final class Tag extends Item\Collection
{
	protected static function getItemClass()
	{
		return Item\Task\Tag::getClass();
	}

	/**
	 * @param $value
	 * @param Item $item
	 */
	public function updateValue($value, $item)
	{
		// todo
	}

	public function joinNames($separator = ',')
	{
		$result = array();
		foreach($this->values as $item)
		{
			if($item['NAME'] != '')
			{
				$result[] = $item['NAME'];
			}
		}

		return implode($separator, $result);
	}
}