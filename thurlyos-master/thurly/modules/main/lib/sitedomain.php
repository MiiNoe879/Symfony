<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Main;

use Thurly\Main\Entity;

class SiteDomainTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_lang_domain';
	}

	public static function getMap()
	{
		return array(
			'LID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'DOMAIN' => array(
				'data_type' => 'string'
			),
			'SITE' => array(
				'data_type' => 'Thurly\Main\Site',
				'reference' => array('=this.LID' => 'ref.LID'),
			),
		);
	}
}
