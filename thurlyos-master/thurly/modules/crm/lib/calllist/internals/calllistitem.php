<?php

namespace Thurly\Crm\CallList\Internals;

use Thurly\Crm\CallList\CallList;
use Thurly\Main\DB;
use Thurly\Main\Entity;
use Thurly\Main\Type;

class CallListItemTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_crm_call_list_item';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return array(
			'LIST_ID' => new Entity\IntegerField('LIST_ID', array(
				'primary' => true
			)),
			'ELEMENT_ID' => new Entity\IntegerField('ELEMENT_ID', array(
				'primary' => true
			)),
			'STATUS_ID' => new Entity\StringField('STATUS_ID', array(
				'required' => true,
				'default_value' => CallList::STATUS_IN_WORK
			)),
			'CALL_ID' => new Entity\IntegerField('CALL_ID'),
			'WEBFORM_RESULT_ID' => new Entity\IntegerField('WEBFORM_RESULT_ID'),
			'RANK' => new Entity\IntegerField('RANK'),
			'WEBFORM_ACTIVITY' => new Entity\ReferenceField(
				'WEBFORM_ACTIVITY',
				'Thurly\Crm\ActivityTable',
				array(
					'=this.WEBFORM_RESULT_ID' => 'ref.ASSOCIATED_ENTITY_ID',
					'=ref.TYPE_ID' =>  new DB\SqlExpression('?i', \CCrmActivityType::Provider),
					'=ref.PROVIDER_ID' => new DB\SqlExpression('?s', \Thurly\Crm\Activity\Provider\WebForm::PROVIDER_ID)
				),
				array('join_type' => 'LEFT')
			),
			'CALL' => new Entity\ReferenceField(
				'CALL',
				'Thurly\Voximplant\StatisticTable',
				array(
					'=this.CALL_ID' => 'ref.ID',
				),
				array('join_type' => 'LEFT')
			),
			'CNT' => new Entity\ExpressionField('CNT', 'COUNT(*)')
		);
	}
}