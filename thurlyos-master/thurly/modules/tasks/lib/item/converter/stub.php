<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * This class is experimental, and should not be used in the client-side code
 *
 * @internal
 */

namespace Thurly\Tasks\Item\Converter;

use Thurly\Tasks\Item;
use Thurly\Tasks\Item\Converter;
use Thurly\Tasks\Util\Collection;

final class Stub extends Converter
{
	public function convert($instance)
	{
		$result = new Result();

		if(Item::isA($instance))
		{
			$instance = clone $instance;
			$instance->setId(0);

			// mark that $instance as a "brand new"
			$cached = $instance->getCachedFields();
			foreach($cached as $field)
			{
				$instance->setFieldModified($field);
			}

			$result->setInstance($instance);
		}
		elseif(Collection::isA($instance))
		{
			$result->setInstance($instance);
		}
		else
		{
			$result->addError('ILLEGAL_SOURCE_INSTANCE', 'Illegal source instance');
		}

		return $result;
	}
}