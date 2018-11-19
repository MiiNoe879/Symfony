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

namespace Thurly\Tasks\Item\Task\Template\Field;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class Access extends \Thurly\Tasks\Item\Field\Collection\Item
{
	protected static function getItemClass()
	{
		return \Thurly\Tasks\Item\Task\Template\Access::getClass();
	}

	public function hasDefaultValue($key, $item)
	{
		return true;
	}

	/**
	 * @param String $key
	 * @param \Thurly\Tasks\Item $item
	 * @return array|mixed
	 */
	public function getDefaultValue($key, $item)
	{
		// grant full rights to the creator
		return $this->createValue(array(
			array(
				'GROUP_CODE' => 'U'.$item->getUserId(),
				'TASK_ID' => static::getAccessLevelFullId(),
			)
		), $key, $item);
	}

	/**
	 * @param mixed $value
	 * @param string $key
	 * @param \Thurly\Tasks\Item $item
	 * @param array $parameters
	 * @return bool
	 */
	public function checkValue($value, $key, $item, array $parameters = array())
	{
		if(parent::checkValue($value, $key, $item, $parameters))
		{
			// at least one user should have full rights
			if($value->find(array(
				'TASK_ID' => static::getAccessLevelFullId()
			))->count() == 0)
			{
				$result = static::obtainResultInstance($parameters);
				if($result)
				{
					$result->addError('ILLEGAL_ACCESS.NO_OWNER', Loc::getMessage('TASKS_ITEM_TASK_TEMPLATE_FIELD_SE_ACCESS_ILLEGAL_OWNER'));
				}
			}

			return true;
		}

		return false;
	}

	private static function getAccessLevelFullId()
	{
		$level = \Thurly\Tasks\Util\User::getAccessLevel('task_template', 'full');

		return $level['ID'];
	}
}