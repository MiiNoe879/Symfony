<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Sale\Internals;

use Thurly\Main,
	Thurly\Main\Localization\Loc;

class BasketArchivePackedTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_basket_archive_packed';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField(
				'BASKET_ARCHIVE_ID',
				array(
					'primary' => true,
				)
			),

			new Main\Entity\StringField('BASKET_DATA')
		);
	}
}