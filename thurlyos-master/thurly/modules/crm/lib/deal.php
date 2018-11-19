<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Crm;

use Thurly\Main\DB;
use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DealTable extends Entity\DataManager
{
	public static function getUfId()
	{
		return 'CRM_DEAL';
	}

	public static function getMap()
	{
		global $DB;

		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'OPPORTUNITY' => array(
				'data_type' => 'integer'
			),
			'CURRENCY_ID' => array(
				'data_type' => 'string'
			),
			'OPPORTUNITY_ACCOUNT' => array(
				'data_type' => 'integer'
			),
			'ACCOUNT_CURRENCY_ID' => array(
				'data_type' => 'string'
			),
			'EXCH_RATE' => array(
				'data_type' => 'integer'
			),
			'PROBABILITY' => array(
				'data_type' => 'integer'
			),
			'STAGE_ID' => array(
				'data_type' => 'string'
			),
			'STAGE_BY' => array(
				'data_type' => 'Status',
				'reference' => array(
					'=this.STAGE_ID' => 'ref.STATUS_ID',
					'=ref.ENTITY_ID' => array('?', 'DEAL_STAGE')
				)
			),
			'CLOSED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'IS_RECURRING' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'TYPE_ID' => array(
				'data_type' => 'string'
			),
			'TYPE_BY' => array(
				'data_type' => 'Status',
				'reference' => array(
					'=this.TYPE_ID' => 'ref.STATUS_ID',
					'=ref.ENTITY_ID' => array('?', 'DEAL_TYPE')
				)
			),
			'COMMENTS' => array(
				'data_type' => 'string'
			),
			'BEGINDATE' => array(
				'data_type' => 'datetime'
			),
			'BEGINDATE_SHORT' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->datetimeToDateFunction('%s'), 'BEGINDATE'
				)
			),
			'CLOSEDATE' => array(
				'data_type' => 'datetime'
			),
			'CLOSEDATE_SHORT' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->datetimeToDateFunction('%s'), 'CLOSEDATE'
				)
			),
			'EVENT_DATE' => array(
				'data_type' => 'datetime'
			),
			'EVENT_DATE_SHORT' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->datetimeToDateFunction('%s'), 'EVENT_DATE'
				)
			),
			'EVENT_ID' => array(
				'data_type' => 'string'
			),
			'EVENT_BY' => array(
				'data_type' => 'Status',
				'reference' => array(
					'=this.EVENT_ID' => 'ref.STATUS_ID',
					'=ref.ENTITY_ID' => array('?', 'EVENT_TYPE')
				)
			),
			'EVENT_DESCRIPTION' => array(
				'data_type' => 'string'
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_CREATE_SHORT' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->datetimeToDateFunction('%s'), 'DATE_CREATE'
				)
			),
			'DATE_MODIFY' => array(
				'data_type' => 'datetime'
			),
			'DATE_MODIFY_SHORT' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->datetimeToDateFunction('%s'), 'DATE_MODIFY'
				)
			),
			'ASSIGNED_BY_ID' => array(
				'data_type' => 'integer'
			),
			'ASSIGNED_BY' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.ASSIGNED_BY_ID' => 'ref.ID')
			),
			'CREATED_BY_ID' => array(
				'data_type' => 'integer'
			),
			'CREATED_BY' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.CREATED_BY_ID' => 'ref.ID')
			),
			'MODIFY_BY_ID' => array(
				'data_type' => 'integer'
			),
			'MODIFY_BY' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.MODIFY_BY_ID' => 'ref.ID')
			),
			'EVENT_RELATION' => array(
				'data_type' => 'EventRelations',
				'reference' => array('=this.ID' => 'ref.ENTITY_ID')
			),
			'LEAD_ID' => array(
				'data_type' => 'integer'
			),
			'LEAD_BY' => array(
				'data_type' => 'Lead',
				'reference' => array('=this.LEAD_ID' => 'ref.ID')
			),
			'CONTACT_ID' => array(
				'data_type' => 'integer'
			),
			'CONTACT_BY' => array(
				'data_type' => 'Contact',
				'reference' => array('=this.CONTACT_ID' => 'ref.ID')
			),
			'COMPANY_ID' => array(
				'data_type' => 'integer'
			),
			'COMPANY_BY' => array(
				'data_type' => 'Company',
				'reference' => array('=this.COMPANY_ID' => 'ref.ID')
			),
			'IS_WORK' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s = \'P\' THEN 1 ELSE 0 END',
					'STAGE_SEMANTIC_ID'
				),
				'values' => array(0, 1)
			),
			'IS_WON' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s = \'S\' THEN 1 ELSE 0 END',
					'STAGE_SEMANTIC_ID'
				),
				'values' => array(0, 1)
			),
			'IS_LOSE' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s = \'F\' THEN 1 ELSE 0 END',
					'STAGE_SEMANTIC_ID'
				),
				'values' => array(0, 1)
			),
			'RECEIVED_AMOUNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'CASE WHEN %s = \'S\' THEN %s ELSE 0 END',
					'STAGE_SEMANTIC_ID', 'OPPORTUNITY_ACCOUNT'
				)
			),
			'LOST_AMOUNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'CASE WHEN %s = \'F\' THEN %s ELSE 0 END',
					'STAGE_SEMANTIC_ID', 'OPPORTUNITY_ACCOUNT'
				)
			),
			'HAS_PRODUCTS' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN EXISTS (SELECT ID FROM b_crm_product_row WHERE OWNER_ID = %s AND OWNER_TYPE = \'D\') THEN 1 ELSE 0 END',
					'ID'
				),
				'values' => array(0, 1)
			),
			'CATEGORY_ID' => array(
				'data_type' => 'integer'
			),
			'STAGE_SEMANTIC_ID' => array(
				'data_type' => 'string'
			),
			'SEARCH_CONTENT' => array(
				'data_type' => 'string'
			),
			'ORIGIN_ID' => array(
				'data_type' => 'string'
			),
			'ORIGINATOR_ID' => array(
				'data_type' => 'string'
			),
			'ORIGINATOR_BY' => array(
				'data_type' => 'ExternalSale',
				'reference' => array('=this.ORIGINATOR_ID' => 'ref.ID')
			)
		);
	}
}