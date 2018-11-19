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

use Thurly\Main\ArgumentTypeException;
use Thurly\Tasks\Item\Collection;
use Thurly\Tasks\Item\Result;
use Thurly\Tasks\Item\SubItem;
use Thurly\Tasks\Util\Type;

abstract class Item extends \Thurly\Tasks\Item\Field\Collection
{
	/**
	 * @return null|\Thurly\Tasks\Item
	 */
	protected static function getItemClass()
	{
		return null;
	}

	/**
	 * @return Collection
	 */
	protected static function getItemCollectionClass()
	{
		/** @var \Thurly\Tasks\Item $seClass */
		$seClass = static::getItemClass();
		return $seClass::getCollectionClass();
	}

	public function hasDefaultValue($key, $item)
	{
		// todo: for tablet or uf field we can definitely tell if there is a default value
		// todo: for custom fields we need to specify it manually
		return parent::hasDefaultValue($key, $item);
	}

	public function getDefaultValue($key, $item)
	{
		return $this->createValue(array(), $key, $item);
	}

	/**
	 * @param $key
	 * @param \Thurly\Tasks\Item $item
	 * @return array|mixed
	 * @throws ArgumentTypeException
	 */
	public function readValueFromDatabase($key, $item)
	{
		$id = $item->getId();
		if($id)
		{
			/** @var \Thurly\Tasks\Item|\Thurly\Tasks\Item\SubItem $itemClass */
			$itemClass = static::getItemClass();
			if(method_exists($itemClass, 'findByParent')) // todo: use SubItem::isA() here, when moved to 5.4
			{
				$value = $itemClass::findByParent($id, array(), array('USER_ID' => $item->getUserId()));
			}
			else
			{
				throw new ArgumentTypeException('There should be a sub-item class: '.$key);
			}
		}
		else
		{
			$value = array();
		}

		return $this->translateValueFromDatabase($value, $key, $item);
	}

	protected function onBeforeSaveToDataBase($value, $key, $item)
	{
	}

	/**
	 * @param $value
	 * @param $key
	 * @param \Thurly\Tasks\Item $item
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
		$isUpdate = $itemState->isModeUpdate();
		$isDelete = $itemState->isModeDelete();
		$newCodePattern = $this->getName().'.#CODE#';

		$this->onBeforeSaveToDataBase($value, $key, $item);

		$itemClass::enterBatchState();

		if($isUpdate || $isDelete)
		{
			if(method_exists($itemClass, 'findByParent')) // todo: use SubItem::isA() here, when moved to 5.4
			{
				$all = $itemClass::findByParent($itemId, array('select' => array('ID')), array(
					'USER_ID' => $item->getUserId()
				));

				/** @var SubItem $exItem */
				foreach($all as $exItem)
				{
					if($isUpdate && ($value && $value->containsId($exItem->getId())))
					{
						continue;
					}

					$exItem->setParent($item);
					$delResult = $exItem->delete();

					$errors->load($delResult->getErrors()->transform(array('CODE' => $newCodePattern)));
				}
			}
			else
			{
				throw new ArgumentTypeException('There should be a sub-item class: '.$key);
			}
		}

		if(($isCreate || $isUpdate) && $value)
		{
			foreach($value as $subItem)
			{
				// save each item of this collection separately
				/** @var SubItem $subItem */
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

	/**
	 * @param mixed[]|\Thurly\Tasks\Util\Collection|Collection $value
	 * @param $key
	 * @param $item
	 * @return array
	 */
	public function createValue($value, $key, $item)
	{
		$collectionClass = static::getItemCollectionClass();

		if(!$collectionClass::isA($value))
		{
			// need to make one...
			$legalValue = new $collectionClass();
			$itemClass = static::getItemClass();

			// i can convert (array or simple collection) of (arrays or collections or items) into $collectionClass
			if(is_array($value) || \Thurly\Tasks\Util\Collection::isA($value))
			{
				/**
				 * @var \Thurly\Tasks\Item|\Thurly\Tasks\Util\Collection|array $v
				 */
				foreach($value as $k => $v)
				{
					if($itemClass::isA($v))
					{
						$legalValue[$k] = $v;
					}
					elseif(is_array($v))
					{
						$legalValue[$k] = new $itemClass($v);
					}
					elseif(\Thurly\Tasks\Util\Collection::isA($v))
					{
						$legalValue[$k] = new $itemClass($v->toArray());
					}
				}
			}

			$value = $legalValue;
		}

		if(count($value))
		{
			/**
			 * @var \Thurly\Tasks\Item\SubItem $v
			 */
			foreach($value as $v)
			{
				$v->setParent($item);
			}
		}

		return $value;
	}

	public function translateValueFromOutside($value, $key, $item)
	{
		if(Type::isIterable($value) && count($value))
		{
			$itemClass = static::getItemClass();

			// todo: possible redundant code, due to contents of createValue()
			foreach($value as $k => $v)
			{
				// strip away broken values: can only accept array, item or simple collection
				if(!is_array($v) && !$itemClass::isA($v) && !\Thurly\Tasks\Util\Collection::isA($v))
				{
					unset($value[$k]);
				}
			}
		}
		else
		{
			// todo: ??
		}

		return $this->createValue($value, $key, $item);
	}

	public function setValue($value, $key, $item, array $parameters = array())
	{
		$value = $this->makeValueSafe($value, $key, $item, $parameters);

		if($this->isCacheable())
		{
			$keepExisting = array_key_exists('KEEP_EXISTING_VALUE', $parameters) && $parameters['KEEP_EXISTING_VALUE'] === true;

			if(!$item->containsKey($key) || !$keepExisting)
			{
				if(array_key_exists('ACTUALIZE_COLLECTIONS', $parameters = array()))
				{
					$prevValue = $item[$key];

					// update items in $value with data of items in $prevValue
					if(count($prevValue) && count($value))
					{
						foreach($value as $k => $subItem)
						{
							$prevSubItem = $prevValue->getItemById($subItem->getId());
							if($prevSubItem)
							{
								// merge new to old, keep old
								$prevSubItem->setData($subItem->getData('~'), $parameters);
								$value[$k] = $prevSubItem;
							}
						}
					}
				}
				else
				{
					$item->offsetSetDirect($key, $value);
				}
			}
		}

		return $value;
	}

	public function prepareValue($value, $key, $item, array $parameters = array())
	{
		// todo: go deeper, like in setValue()
		return $value;
	}

	public function checkValue($value, $key, $item, array $parameters = array())
	{
		$result = static::obtainResultInstance($parameters);

		// check each element of the collection
		foreach($value as $subItem)
		{
			$subItem->checkData($result);
		}

		return $result->isSuccess();
	}
}