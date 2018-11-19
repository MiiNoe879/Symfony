<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task;

use Thurly\Tasks\Internals\Task\CheckListTable;
use Thurly\Tasks\UI;
use Thurly\Tasks\Item\Task\Collection;

/**
 * Class CheckList
 * @package Thurly\Tasks\Item\Task
 *
 * todo: implement here additional fields: CHECKED and SORT, to be able to copy directly template checklist to task checklist
 */
final class CheckList extends \Thurly\Tasks\Item\SubItem
{
	protected static function getParentConnectorField()
	{
		return 'TASK_ID';
	}

	public static function getDataSourceClass()
	{
		return CheckListTable::getClass();
	}

	public static function getCollectionClass()
	{
		return Collection\CheckList::getClass();
	}

	public function getFieldTitleHTML()
	{
		return UI::convertBBCodeToHtmlSimple($this['TITLE']);
	}

	public static function findByParent($parentId, array $parameters = array(), $settings = null)
	{
		if(!array_key_exists('order', $parameters))
		{
			$parameters['order'] = array('SORT_INDEX' => 'asc');
		}

		return parent::findByParent($parentId, $parameters, $settings);
	}

	/**
	 * Do some data rearrangements before save() performed
	 *
	 * @param \Thurly\Tasks\Item\Result $result
	 * @return boolean
	 * @access private
	 */
	public function prepareData($result)
	{
		if(parent::prepareData($result))
		{
			$id = $this->getId();

			if(!$id)
			{
				if(!$this->isFieldModified('CREATED_BY'))
				{
					$this['CREATED_BY'] = $this->getUserId();
				}
			}
			else
			{
				if($this->isFieldModified('IS_COMPLETE'))
				{
					$completeNow = $this['IS_COMPLETE'];
					$completeThen = $this->offsetGetPristine('IS_COMPLETE');
					if($completeNow == 'Y' && $completeThen == 'N')
					{
						if(!$this->isFieldModified('TOGGLED_BY'))
						{
							$this['TOGGLED_BY'] = $this->getUserId();
						}
						if(!$this->isFieldModified('TOGGLED_DATE'))
						{
							$this['TOGGLED_DATE'] = new \Thurly\Main\Type\DateTime();
						}
					}
				}
			}
		}

		return $result->isSuccess();
	}

	public function isCompleted()
	{
		return $this['IS_COMPLETE'] == 'Y';
	}
}