<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item;

use Thurly\Tasks\Internals\SystemLogTable;
use Thurly\Tasks\UI;
use Thurly\Tasks\Util\Type;
use Thurly\Tasks\Util\Error;
use Thurly\Main\Type\DateTime;
use Thurly\Tasks\Util\User;

final class SystemLog extends \Thurly\Tasks\Item
{
	const TYPE_NOTICE = 1;
	const TYPE_WARNING = 2;
	const TYPE_ERROR = 3;

	public static function getDataSourceClass()
	{
		return SystemLogTable::getClass();
	}

	protected static function generateMap(array $parameters = array())
	{
		$map = parent::generateMap(array(
			'EXCLUDE' => array(
				// will be overwritten below
				'ERROR' => true,
			)
		));

		$map->placeFields(array(
			// override some tablet fields
			'ERROR' => new Field\Collection\Error(array(
				'NAME' => 'ERROR',

				'SOURCE' => Field\Scalar::SOURCE_TABLET,
				'DB_READABLE' => true,
				'DB_WRITABLE' => true,
			))
		));

		return $map;
	}

	public function prepareData($result)
	{
		$id = $this->getId();
		if(!$id)
		{
			$now = new \Thurly\Main\Type\DateTime();

			if(!$this->isFieldModified('CREATED_DATE')) // created date was not set manually
			{
				$this['CREATED_DATE'] = $now;
			}
			if(!$this->isFieldModified('TYPE')) // set type from error collection contents
			{
				$this['TYPE'] = static::TYPE_NOTICE;
				if(!$this['ERROR']->isEmpty())
				{
					$this['TYPE'] = $this['ERROR']->filter(array('TYPE' => Error::TYPE_FATAL))->isEmpty() ? static::TYPE_WARNING : static::TYPE_ERROR;
				}
			}
		}

		return $result;
	}

	/**
	 * Rotate log, remove records that are older than month ago
	 *
	 * @throws \Thurly\Main\ArgumentException
	 * @throws \Exception
	 */
	public static function rotate()
	{
		foreach(SystemLogTable::getList(array(
			'filter' => array(
				'<CREATED_DATE' => UI::formatDateTime(User::getTime() - 8640000), // 100 days
			)
		))->fetchAll() as $record)
		{
			SystemLogTable::delete($record['ID']);
		}
	}

	public static function deleteByEntity($entityId, $entityType)
	{
		$match = static::find(array(
			'filter' => array(
				'=ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType
			),
			'select' => array(
				'ID'
			)
		));
		foreach($match as $item)
		{
			$item->delete();
		}
	}
}