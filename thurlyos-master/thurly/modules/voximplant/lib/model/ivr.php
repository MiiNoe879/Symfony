<?php
namespace Thurly\Voximplant\Model;

use Thurly\Main\Entity;

class IvrTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_voximplant_ivr';
	}
	
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			'NAME' => new Entity\StringField('NAME', array(
				'size' => '255'
			)),
			'FIRST_ITEM_ID' => new Entity\IntegerField('FIRST_ITEM_ID'),
		);
	}
}