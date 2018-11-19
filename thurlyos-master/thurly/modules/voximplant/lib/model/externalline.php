<?php

namespace Thurly\Voximplant\Model;

use Thurly\Main\Entity;
use Thurly\Main\Type\DateTime;

class ExternalLineTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_voximplant_external_line';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'auto_complete' => true
			)),
			new Entity\StringField('NUMBER'),
			new Entity\StringField('NAME'),
			new Entity\IntegerField('REST_APP_ID'),
			new Entity\DateTimeField('DATE_CREATE', array(
				'default_value' => function()
				{
					return new DateTime();
				}
			))
		);
	}
}