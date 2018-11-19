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

class SectionTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_iblock_section';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'NAME' => array(
				'data_type' => 'string'
			)
		);

		return $fieldsMap;
	}
}
