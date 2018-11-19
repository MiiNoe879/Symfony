<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2014 Thurly
 */
namespace Thurly\Sale\Internals;

use	Thurly\Main;

class OrderPropsRelationTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_order_props_relation';
	}

	public static function getMap()
	{
		return array(
			'PROPERTY_ID' => array(
				'primary' => true,
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'ENTITY_ID' => array(
				'primary' => true,
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'getEntityValidators'),
			),
			'ENTITY_TYPE' => array(
				'primary' => true,
				'data_type' => 'boolean',
				'values' => array('P', 'D'),
			),

			'lPROPERTY' => array(
				'data_type' => 'Thurly\Sale\Internals\OrderPropsTable',
				'reference' => array('=this.PROPERTY_ID' => 'ref.ID'),
				'join_type' => 'LEFT',
			),
		);
	}

	public static function getEntityValidators()
	{
		return array(
			new Main\Entity\Validator\Length(1, 35),
		);
	}
}
