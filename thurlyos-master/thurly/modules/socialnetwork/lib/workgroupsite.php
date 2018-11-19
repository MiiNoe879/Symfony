<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage socialnetwork
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Socialnetwork;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class WorkgroupSiteTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_site';
	}

	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'GROUP' => array(
				'data_type' => '\Thurly\Socialnetwork\Workgroup',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'SITE' => array(
				'data_type' => '\Thurly\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID')
			),
		);
	}
}
