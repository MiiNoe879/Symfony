<?
namespace Thurly\Crm\SiteButton\Internals;

use	Thurly\Main\Localization\Loc;
use Thurly\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class AvatarTable extends \Thurly\Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_crm_button_avatar';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			)
		);
	}
}