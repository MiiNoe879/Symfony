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

use Thurly\Tasks\Util\Type;
use Thurly\Tasks\Util;

class Scalar extends \Thurly\Tasks\Item\Field\Collection
{
	protected $dbSerialized = false;

	public function __construct(array $parameters)
	{
		if(array_key_exists('DB_SERIALIZED', $parameters))
		{
			$this->dbSerialized = !!$parameters['DB_SERIALIZED'];
		}

		parent::__construct($parameters);
	}

	public function translateValueFromDatabase($value, $key, $item)
	{
		return $this->createValue(
			$this->dbSerialized ? Type::unSerializeArray($value) : $value,
			$key, $item);
	}

	public function translateValueToDatabase($value, $key, $item)
	{
		$value = $value->toArray();

		if($this->dbSerialized)
		{
			$value = Type::serializeArray($value);
		}

		return $value;
	}

	public function createValue($value, $key, $item)
	{
		$collectionClass = static::getItemCollectionClass();

		if($collectionClass::isA($value))
		{
			return $value;
		}

		if($value == null)
		{
			$value = array();
		}
		elseif(!is_array($value))
		{
			$value = (array) $value;
		}

		// 0 is also allowed!
		return new $collectionClass($this->clearArray($value));
	}

	protected function clearArray($value)
	{
		return array_values(array_unique($value));
	}
}