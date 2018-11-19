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

namespace Thurly\Tasks\Item\Task\Field;

use Thurly\Main\ArgumentTypeException;
use Thurly\Tasks\Item;
use Thurly\Tasks\Item\Result;
use Thurly\Tasks\Item\SubItem;

class Member extends \Thurly\Tasks\Item\Field\Collection\Item
{
	protected static function getItemClass()
	{
		return Item\Task\Member::getClass();
	}

	/**
	 * Entity "member" does not have single primary key, so different procedure here
	 *
	 * @param $value
	 * @param $key
	 * @param Item $item
	 * @return Result
	 * @throws ArgumentTypeException
	 */
	public function saveValueToDataBase($value, $key, $item)
	{
		$value = $this->translateValueToDatabase($value, $key, $item);

		$result = new Result();
		$errors = $result->getErrors();

		$itemId = $item->getId();
		/** @var SubItem $itemClass */
		$itemClass = static::getItemClass();
		$itemState = $item->getTransitionState();
		$isCreate = $itemState->isModeCreate();
		$isDelete = $itemState->isModeDelete();
		$newCodePattern = $this->getName().'.#CODE#';

		$this->onBeforeSaveToDataBase($value, $key, $item);

		$itemClass::enterBatchState();

		if(!$isCreate)
		{
			// delete all, because we do not have simple primary key to delete by
			$itemClass::deleteByParent($itemId);
		}

		if(!$isDelete)
		{
			// now add again...
			/** @var SubItem $subItem */
			foreach($value as $subItem)
			{
				// save each item of this collection separately
				$subItem->setParentId($itemId);
				$saveResult = $subItem->save(array(
					'KEEP_DATA' => true,
				));

				$errors->load($saveResult->getErrors()->transform(array('CODE' => $newCodePattern)));
			}
		}

		$itemClass::leaveBatchState();

		return $result;
	}
}