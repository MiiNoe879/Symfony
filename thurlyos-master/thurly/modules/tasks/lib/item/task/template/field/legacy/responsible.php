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

final class Responsible extends \Thurly\Tasks\Item\Field\Integer
{
	public function getValue($key, $item, array $parameters = array())
	{
		if(is_object($item['RESPONSIBLES']))
		{
			return $item['RESPONSIBLES']->first();
		}

		return false;
	}

	public function setValue($value, $key, $item, array $parameters = array())
	{
		$resp = $item['RESPONSIBLES']->toArray();
		array_shift($resp);

		if($value)
		{
			array_unshift($resp, $value);
		}

		$item['RESPONSIBLES']->set($resp);
	}
}