<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Location;

use Thurly\Main;
use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

use Thurly\Sale\Location\Name;
use Thurly\Sale\Location\Util\Assert;
use Thurly\Sale\Result;

Loc::loadMessages(__FILE__);

class TypeTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_type';
	}

	public static function add(array $data)
	{
		$res = self::getList(array(
			'filter' => array('=CODE' => $data['CODE'])
		));
		
		if($res->fetch())
		{
			$addResult = new Entity\AddResult();
			$addResult->addError(new Main\Error(Loc::getMessage('SALE_LOCATION_TYPE_ENTITY_CODE_FIELD_EXIST_ERROR')));
			return $addResult;
		}

		if(isset($data['NAME']))
		{
			$name = $data['NAME'];
			unset($data['NAME']);
		}

		if((string) $data['DISPLAY_SORT'] == '' && (string) $data['SORT'] != '')
		{
			$data['DISPLAY_SORT'] = $data['SORT'];
		}

		$addResult = parent::add($data);

		// add connected data
		if($addResult->isSuccess())
		{
			$primary = $addResult->getId();

			// names
			if(isset($name))
				Name\TypeTable::addMultipleForOwner($primary, $name);
		}

		return $addResult;
	}
	
	public static function update($primary, array $data)
	{
		$primary = Assert::expectIntegerPositive($primary, '$primary');

		if(isset($data['CODE']))
		{
			$res = self::getList(array(
				'filter' => array(
					'=CODE' => $data['CODE'],
					'!=ID' => $primary
				)
			));

			if($res->fetch())
			{
				$updResult = new Entity\UpdateResult();
				$updResult->addError(new Main\Error(Loc::getMessage('SALE_LOCATION_TYPE_ENTITY_CODE_FIELD_EXIST_ERROR')));
				return $updResult;
			}
		}

		// first update parent, and if it succeed, do updates of the connected data

		if(isset($data['NAME']))
		{
			$name = $data['NAME'];
			unset($data['NAME']);
		}

		$updResult = parent::update($primary, $data);

		// update connected data
		if($updResult->isSuccess())
		{
			// names
			if(isset($name))
				Name\TypeTable::updateMultipleForOwner($primary, $name);
		}

		return $updResult;
	}

	public static function delete($primary)
	{
		$primary = Assert::expectIntegerPositive($primary, '$primary');

		$delResult = parent::delete($primary);

		// delete connected data
		if($delResult->isSuccess())
			Name\TypeTable::deleteMultipleForOwner($primary);

		return $delResult;
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_TYPE_ENTITY_CODE_FIELD')
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SALE_LOCATION_TYPE_ENTITY_DEPTH_LEVEL_FIELD')
			),

			'DISPLAY_SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SALE_LOCATION_TYPE_ENTITY_DISPLAY_SORT_FIELD')
			),

			// virtual
			'NAME' => array(
				'data_type' => 'Thurly\Sale\Location\Name\Type',
				'reference' => array(
					'=this.ID' => 'ref.TYPE_ID'
				),
			),
			'LOCATION' => array(
				'data_type' => 'Thurly\Sale\Location\Location',
				'reference' => array(
					'=this.ID' => 'ref.TYPE_ID'
				),
			)
		);
	}
}
