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

namespace Thurly\Tasks\Item\Task\Collection;

use Thurly\Tasks\Item;
use Thurly\Tasks\Util\Result;

class CheckList extends \Thurly\Tasks\Item\Collection
{
	protected static function getItemClass()
	{
		return \Thurly\Tasks\Item\Task\CheckList::getClass();
	}

	protected static function getSortColumnName()
	{
		return 'SORT_INDEX';
	}

	protected static function getCheckedColumnName()
	{
		return 'IS_COMPLETE';
	}

	/**
	 * Removes holes in sort index
	 */
	public function sealSortIndex()
	{
		if($this->count())
		{
			$sortCol = static::getSortColumnName();

			$hasSort = false;
			/**
			 * @var Item $v
			 */
			foreach($this->values as $k => $v)
			{
				if($v->containsKey($sortCol))
				{
					$hasSort = true;
					break;
				}
			}

			if($hasSort)
			{
				// some of items contain SORT column, so we have to sort by it first
				// otherwise we will leave the origin order
				$this->sort(array($sortCol => 'asc'));
			}

			$sort = 0;
			foreach($this->values as $k => $v)
			{
				$v[$sortCol] = $sort++;
			}
		}
	}

	/**
	 * Moves one item after another item
	 *
	 * @param $id
	 * @param $afterId
	 * @return Result
	 */
	public function moveItemAfter($id, $afterId)
	{
		$result = new Result();

		if(!$this->containsId($id))
		{
			$result->addError('ITEM_NOT_FOUND', 'Item with ID='.$id.' not found in the collection.');
		}

		$afterId = intval($afterId);
		if($afterId && !$this->containsId($afterId))
		{
			$result->addError('ITEM_NOT_FOUND', 'Item with ID='.$afterId.' not found in the collection.');
		}

		if($result->isSuccess())
		{
			$ix = $this->getItemIndexById($id);
			$item = $this->getItemById($id);

			// it is a mess...
			if($afterId)
			{
				$ixAfter = $this->getItemIndexById($afterId);
				if($ixAfter == (count($this->values) - 1))
				{
					unset($this->values[$ix]);
					$this->values[] = $item;
				}
				else
				{
					unset($this->values[$ix]);
					$newVal = array();
					foreach($this->values as $k => $v)
					{
						$newVal[] = $v;

						if($k == $ixAfter)
						{
							$newVal[] = $item;
						}
					}

					$this->values = $newVal;
				}
			}
			else
			{
				unset($this->values[$ix]);
				array_unshift($this->values, $item);
			}

			$this->onChange();
			$this->setSortByValueOrder();
		}

		return $result;
	}

	private function setSortByValueOrder()
	{
		$sort = 0;
		foreach($this->values as $item)
		{
			$item[static::getSortColumnName()] = $sort++;
		}
	}
}