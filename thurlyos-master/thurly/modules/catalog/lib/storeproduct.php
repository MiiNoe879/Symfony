<?php
namespace Thurly\Catalog;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class StoreProductTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PRODUCT_ID int mandatory
 * <li> AMOUNT double mandatory
 * <li> STORE_ID int mandatory
 * <li> STORE reference to {@link \Thurly\Catalog\StoreTable}
 * <li> PRODUCT reference to {@link \Thurly\Catalog\ProductTable}
 * </ul>
 *
 * @package Thurly\Catalog
 **/

class StoreProductTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_store_product';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_ID_FIELD')
			)),
			'STORE_ID' => new Main\Entity\IntegerField('STORE_ID', array(
				'required' => true,
				'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_STORE_ID_FIELD')
			)),
			'PRODUCT_ID' => new Main\Entity\IntegerField('PRODUCT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_PRODUCT_ID_FIELD')
			)),
			'AMOUNT' => new Main\Entity\FloatField('AMOUNT', array(
				'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_AMOUNT_FIELD')
			)),
			'STORE' => new Main\Entity\ReferenceField(
				'STORE',
				'\Thurly\Catalog\Store',
				array('=this.STORE_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'PRODUCT' => new Main\Entity\ReferenceField(
				'PRODUCT',
				'\Thurly\Catalog\Product',
				array('=this.PRODUCT_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			)
		);
	}
}