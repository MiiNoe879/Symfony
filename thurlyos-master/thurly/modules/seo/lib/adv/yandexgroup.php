<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage seo
 * @copyright 2001-2013 Thurly
 */
namespace Thurly\Seo\Adv;

use Thurly\Main\Localization\Loc;
use Thurly\Seo\AdvEntity;

Loc::loadMessages(__FILE__);

/**
 * Class YandexGroupTable
 *
 * Local mirror for Yandex.Direct banner groups
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENGINE_ID int mandatory
 * <li> XML_ID string(255) mandatory
 * <li> LAST_UPDATE datetime optional
 * <li> SETTINGS string optional
 * <li> CAMPAIGN_ID int mandatory
 * </ul>
 *
 * @package Thurly\Seo
 **/

class YandexGroupTable extends AdvEntity
{
	const ENGINE = 'yandex_direct';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_seo_adv_group';
	}

	public static function getMap()
	{
		return array_merge(
			parent::getMap(),
			array(
				'CAMPAIGN_ID' => array(
					'data_type' => 'integer',
					'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_CAMPAIGN_ID_FIELD'),
				),
				'CAMPAIGN' => array(
					'data_type' => 'Thurly\Seo\Adv\YandexCampaignTable',
					'reference' => array('=this.CAMPAIGN_ID' => 'ref.ID'),
				),
			)
		);
	}
}
