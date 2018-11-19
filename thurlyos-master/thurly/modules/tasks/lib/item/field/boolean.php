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

namespace Thurly\Tasks\Item\Field;

use Thurly\Tasks\Item;

class Boolean extends Scalar
{
	protected $enum = array();

	public function __construct(array $parameters)
	{
		parent::__construct($parameters);

		if(array_key_exists('ENUMERATION', $parameters))
		{
			$this->setEnumeration($parameters['ENUMERATION']);
		}
	}

	protected function setEnumeration($enum)
	{
		if(is_array($enum))
		{
			$this->enum = $enum;
		}
	}

	/**
	 * Returns value that definitely represents the required type
	 *
	 * @param $value
	 * @param $key
	 * @param Item $item
	 * @return mixed
	 */
	public function createValue($value, $key, $item)
	{
		if(!in_array($value, $this->enum))
		{
			if($this->hasDefaultValue($key, $item))
			{
				$value = $this->getDefaultValue($key, $item);
			}
			elseif(count($this->enum))
			{
				reset($this->enum);
				$value = next($this->enum);
				reset($this->enum);
			}
			else
			{
				$value = '';
			}
		}

		return $value;
	}
}