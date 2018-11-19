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

class WorkgroupTemplateRightTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_template_right';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'TEMPLATE_ID' => array(
				'data_type' => 'integer',
			),
			'TEMPLATE' => array(
				'data_type' => '\Thurly\Socialnetwork\WorkgroupTemplate',
				'reference' => array('=this.TEMPLATE_ID' => 'ref.ID')
			),
			'GROUP_CODE' => array(
				'data_type' => 'string'
			),
		);

		return $fieldsMap;
	}
}
