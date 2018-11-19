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

namespace Thurly\Tasks\Item\Field\Collection;

use Thurly\Main\Type\DateTime;
use Thurly\Tasks\Util\Type;
use Thurly\Tasks\Util;
use Thurly\Tasks\UI;

class UFDate extends \Thurly\Tasks\Item\Field\Collection
{
	public function getDefaultValue($key, $item)
	{
		$value = null;
		if(is_array($this->default))
		{
			$type = $this->default['TYPE'];
			$value = $this->default['VALUE'];

			if($type == 'NONE')
			{
				$value = '';
			}
			elseif($type == 'NOW')
			{
				$value = $item->getContext()->getNow();
			}
			elseif($type == 'FIXED')
			{
				$value = new DateTime(UI::formatDateTimeFromDB($value));
			}

			if($value)
			{
				$value = array($value);
			}
		}

		return $this->createValue($value, $key, $item);
	}

	public function translateValueToDatabase($value, $key, $item)
	{
		return $value->toArray();
	}

	public function translateValueFromOutside($value, $key, $item)
	{
		if(Type::isIterable($value))
		{
			foreach($value as $k => $v)
			{
				$value[$k] = Type\DateTime::createFromObjectOrString($v);
			}
		}

		return $this->createValue($value, $key, $item);
	}

	public function createValue($value, $key, $item)
	{
		$collectionClass = static::getItemCollectionClass();

		if($collectionClass::isA($value))
		{
			return $value;
		}

		if(!is_array($value))
		{
			$value = (array) $value;
		}

		foreach($value as $k => $v)
		{
			if(!($v instanceof DateTime))
			{
				unset($value[$k]);
			}
		}

		return new $collectionClass($value);
	}
}