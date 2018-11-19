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

namespace Thurly\Tasks\Item\Task\Field\Legacy;

use Thurly\Tasks\Internals\Task\TagTable;
use Thurly\Tasks\Item\Result;
use Thurly\Tasks\Util\Collection;

class Tag extends \Thurly\Tasks\Item\Field\Collection
{
	/**
	 * @param string $key
	 * @param \Thurly\Tasks\Item $item
	 * @param array $parameters
	 * @return array|mixed
	 */
	public function getValue($key, $item, array $parameters = array())
	{
		$result = array();
		foreach($item['SE_TAG'] as $tag)
		{
			$result[] = $tag['NAME'];
		}

		return $this->createValue($result, $key, $item);
	}

	/**
	 * @param mixed $value
	 * @param string $key
	 * @param \Thurly\Tasks\Item $item
	 * @param array $parameters
	 * @return mixed
	 */
	public function setValue($value, $key, $item, array $parameters = array())
	{
		$item['SE_TAG'] = $value;
		$item->setFieldModified('SE_TAG');

		return $value;
	}
}