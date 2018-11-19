<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage seoproxy
 * @copyright 2001-2014 Thurly
 */
namespace Thurly\Seo\WebHook\Internals;

use Thurly\Main\Entity;
use Thurly\Main\Security\Random;
use Thurly\Main\Type\DateTime;

class WebHookTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_seo_service_webhook';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
				'required' => true,
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'SECURITY_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => function() {
					return Random::getString(32);
				}
			)
		);
	}
}