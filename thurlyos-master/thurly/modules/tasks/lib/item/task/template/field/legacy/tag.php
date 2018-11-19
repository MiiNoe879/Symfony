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

namespace Thurly\Tasks\Item\Task\Template\Field\Legacy;

use Thurly\Tasks\Util\Type;

class Tag extends \Thurly\Tasks\Item\Field\Collection
{
	public function getValue($key, $item, array $parameters = array())
	{
		$result = array();
		foreach($item['SE_TAG'] as $tag)
		{
			$result[] = $tag['NAME'];
		}

		return $this->createValue($result, $key, $item);
	}

	public function setValue($value, $key, $item)
	{
		$item['SE_TAG'] = $value;
		$item->setFieldModified('SE_TAG');
	}
}