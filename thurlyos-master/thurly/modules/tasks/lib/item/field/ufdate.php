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

use Thurly\Main\Type\DateTime;
use Thurly\Tasks\Util\Type;
use Thurly\Tasks\UI;

final class UFDate extends Date
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
		}

		return $this->createValue($value, $key, $item);
	}

	public function translateValueFromOutside($value, $key, $item)
	{
		return Type\DateTime::createFromObjectOrString($value);
	}
}