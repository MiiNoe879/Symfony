<?php
namespace Thurly\Imopenlines\Model;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class LivechatTable
 *
 * Fields:
 * <ul>
 * <li> CONFIG_ID int mandatory
 * <li> URL_CODE string(255) optional
 * <li> URL_CODE_ID int optional
 * <li> URL_CODE_PUBLIC string(255) optional
 * <li> URL_CODE_PUBLIC_ID int optional
 * <li> TEMPLATE_ID string(255) optional
 * <li> BACKGROUND_IMAGE int optional
 * <li> CSS_ACTIVE bool optional default 'N'
 * <li> CSS_PATH string(255) optional
 * <li> CSS_TEXT string optional
 * <li> COPYRIGHT_REMOVED bool optional default 'N'
 * <li> CACHE_WIDGET_ID int optional
 * <li> CACHE_BUTTON_ID int optional
 * </ul>
 *
 * @package Thurly\Imopenlines
 **/

class LivechatTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_imopenlines_livechat';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CONFIG_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('LIVECHAT_ENTITY_CONFIG_ID_FIELD'),
			),
			'URL_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUrlCode'),
				'title' => Loc::getMessage('LIVECHAT_ENTITY_URL_CODE_FIELD'),
			),
			'URL_CODE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('LIVECHAT_ENTITY_URL_CODE_ID_FIELD'),
			),
			'URL_CODE_PUBLIC' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUrlCodePublic'),
				'title' => Loc::getMessage('LIVECHAT_ENTITY_URL_CODE_PUBLIC_FIELD'),
			),
			'URL_CODE_PUBLIC_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('LIVECHAT_ENTITY_URL_CODE_PUBLIC_ID_FIELD'),
			),
			'TEMPLATE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTemplateId'),
				'title' => Loc::getMessage('LIVECHAT_ENTITY_TEMPLATE_ID_FIELD'),
				'default_value' => 'color',
			),
			'BACKGROUND_IMAGE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('LIVECHAT_ENTITY_BACKGROUND_IMAGE_FIELD'),
				'default_value' => '0',
			),
			'CSS_ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('LIVECHAT_ENTITY_CSS_ACTIVE_FIELD'),
				'default_value' => 'N',
			),
			'CSS_PATH' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCssPath'),
				'title' => Loc::getMessage('LIVECHAT_ENTITY_CSS_PATH_FIELD'),
			),
			'CSS_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('LIVECHAT_ENTITY_CSS_TEXT_FIELD'),
			),
			'COPYRIGHT_REMOVED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('LIVECHAT_ENTITY_COPYRIGHT_REMOVED_FIELD'),
				'default_value' => 'N',
			),
			'CONFIG' => array(
				'data_type' => 'Thurly\ImOpenLines\Model\Config',
				'reference' => array('=this.CONFIG_ID' => 'ref.ID'),
			),
			'CACHE_WIDGET_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('LIVECHAT_ENTITY_CACHE_WIDGET_ID_FIELD'),
			),
			'CACHE_BUTTON_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('LIVECHAT_ENTITY_CACHE_BUTTON_ID_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for URL_CODE field.
	 *
	 * @return array
	 */
	public static function validateUrlCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for URL_CODE_PUBLIC field.
	 *
	 * @return array
	 */
	public static function validateUrlCodePublic()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for TEMPLATE_ID field.
	 *
	 * @return array
	 */
	public static function validateTemplateId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CSS_PATH field.
	 *
	 * @return array
	 */
	public static function validateCssPath()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}