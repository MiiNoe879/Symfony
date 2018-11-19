<?php
namespace Thurly\Crm\Category\Entity;
use Thurly\Main;
use Thurly\Main\Entity;
class DealCategoryTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_crm_deal_category';
	}
	public static function getMap()
	{
		return array(
			'ID' => array('data_type' => 'integer', 'primary' => true, 'autocomplete' => true),
			'CREATED_DATE' => array('data_type' => 'date', 'required' => true),
			'NAME' => array('data_type' => 'string'),
			'IS_LOCKED' => array('data_type' => 'boolean', 'values' => array('N', 'Y'), 'default_value' => 'N'),
			'SORT' => array('data_type' => 'integer')
		);
	}
}