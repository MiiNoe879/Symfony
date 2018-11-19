<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage blog
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Blog;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PostSocnetRightsTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_blog_socnet_rights';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'POST_ID' => array(
				'data_type' => 'integer',
			),
			'POST' => array(
				'data_type' => '\Thurly\Blog\Post',
				'reference' => array('=this.POST_ID' => 'ref.ID')
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string'
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
			),
			'ENTITY' => array(
				'data_type' => 'string'
			),
		);

		return $fieldsMap;
	}
}
