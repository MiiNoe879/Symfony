<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Sale;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class GoodsSectionTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_iblock_section_element';
	}

	public static function getMap()
	{
		return array(
			'IBLOCK_ELEMENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'PRODUCT' => array(
				'data_type' => 'Product',
				'reference' => array(
					'=this.IBLOCK_ELEMENT_ID' => 'ref.ID'
				)
			),
			'IBLOCK_SECTION_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'SECT' => array(
				'data_type' => 'Section',
				'reference' => array(
					'=this.IBLOCK_SECTION_ID' => 'ref.ID'
				)
			)
		);
	}
}
