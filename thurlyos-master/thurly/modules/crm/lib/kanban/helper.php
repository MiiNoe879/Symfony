<?php
namespace Thurly\Crm\Kanban;

use \Thurly\Main\Localization\Loc;
use \Thurly\Crm\Counter\EntityCounterType;
use \Thurly\Crm\PhaseSemantics;

Loc::loadMessages(__FILE__);

class Helper
{
	/**
	 * UI Filter prefix.
	 */
	const FILTER_PREFIX = 'KANBAN_V11_';

	/**
	 * Get instance of grid.
	 * @param string $type Type of entity.
	 * @return \CGridOptions
	 */
	public static function getGrid($type)
	{
		static $grid = array();

		if (!array_key_exists($type, $grid))
		{
			$grid[$type] = new \Thurly\Main\UI\Filter\Options(self::FILTER_PREFIX . $type, self::getPresets($type));
		}
		return $grid[$type];
	}

	/**
	 * Get id of grid.
	 * @param string $type Type of entity.
	 * @return string
	 */
	public static function getGridId($type)
	{
		return self::FILTER_PREFIX . $type;
	}

	/**
	 * Get lead sources, deal types, etc.
	 * @param string $code Type ot status.
	 * @return array
	 */
	private function getStatuses($code)
	{
		static $statuses = array();

		if (empty($statuses))
		{
			$statuses[$code] = array();
			foreach (\CCrmStatus::GetStatus($code) as $row)
			{
				$statuses[$code][$row['STATUS_ID']] = $row['NAME'];
			}
		}

		return $statuses[$code];
	}

	/**
	 * Get owner types.
	 * @return array
	 */
	private static function getTypes()
	{
		static $types = null;

		if ($types === null)
		{
			$types = array(
				'lead' => \CCrmOwnerType::LeadName,
				'deal' => \CCrmOwnerType::DealName,
				'quote' => \CCrmOwnerType::QuoteName,
				'invoice' => \CCrmOwnerType::InvoiceName
			);
		}

		return $types;
	}

	/**
	 * Get filter for Kanban.
	 * @param string $entity Type of entity.
	 * @return array
	 */
	public static function getFilter($entity)
	{
		static $filter = array();
		$types = self::getTypes();

		if (!array_key_exists($entity, $filter))
		{
			$filter[$entity] = array();
			// lead
			if ($entity == $types['lead'])
			{
				$filter[$entity]['SOURCE_ID'] = array(
					'id' => 'SOURCE_ID',
					'flt_key' => '=SOURCE_ID',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_SOURCE'),
					'default' => true,
					'type' => 'list',
					'items' => self::getStatuses('SOURCE'),
					'params' => array(
						'multiple' => 'Y'
					)
				);
				$filter[$entity]['DATE_CREATE'] = array(
					'id' => 'DATE_CREATE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_DATE_CREATE'),
					'default' => true,
					'type' => 'date'
				);
				$filter[$entity]['COMMUNICATION_TYPE'] = array(
					'id' => 'COMMUNICATION_TYPE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_COMMUNICATION_TYPE'),
					'default' => true,
					'type' => 'list',
					'items' => \CCrmFieldMulti::PrepareListItems(array(
									\CCrmFieldMulti::PHONE,
									\CCrmFieldMulti::EMAIL
								)),
					'params' => array(
						'multiple' => 'Y'
					)
				);
			}
			// deal
			elseif ($entity == $types['deal'])
			{
				$filter[$entity]['DEAL_TYPE'] = array(
					'id' => 'DEAL_TYPE',
					'flt_key' => '@TYPE_ID',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_DEAL_TYPE'),
					'default' => true,
					'type' => 'list',
					'items' => self::getStatuses('DEAL_TYPE'),
					'params' => array(
						'multiple' => 'Y'
					)
				);
				$filter[$entity]['CONTACT_ID'] = array(
					'id' => 'CONTACT_ID',
					'flt_key' => '=CONTACT_ID',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_CONTACT_ID'),
					'default' => true,
					'type' => 'custom_entity',
					'selector' => array(
						'TYPE' => 'crm_entity',
						'DATA' => array(
							'ID' => 'contact',
							'FIELD_ID' => 'CONTACT_ID',
							'FIELD_ALIAS' => 'CONTACT_ID',
							'ENTITY_TYPE_NAMES' => array(
								\CCrmOwnerType::ContactName
							),
							'IS_MULTIPLE' => 1
						)
					)
				);
				$filter[$entity]['COMPANY_ID'] = array(
					'id' => 'COMPANY_ID',
					'flt_key' => '=COMPANY_ID',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_COMPANY_ID'),
					'default' => true,
					'type' => 'custom_entity',
					'selector' => array(
						'TYPE' => 'crm_entity',
						'DATA' => array(
							'ID' => 'company',
							'FIELD_ID' => 'COMPANY_ID',
							'FIELD_ALIAS' => 'COMPANY_ID',
							'ENTITY_TYPE_NAMES' => array(
								\CCrmOwnerType::CompanyName
							),
							'IS_MULTIPLE' => 1
						)
					)
				);
				$filter[$entity]['BEGINDATE'] = array(
					'id' => 'BEGINDATE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_BEGINDATE_DEAL'),
					'default' => true,
					'type' => 'date'
				);
				$filter[$entity]['CLOSEDATE'] = array(
					'id' => 'CLOSEDATE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_CLOSEDATE_DEAL'),
					'default' => true,
					'type' => 'date'
				);
				$filter[$entity]['OPPORTUNITY'] = array(
					'id' => 'OPPORTUNITY',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_OPPORTUNITY'),
					'default' => true,
					'type' => 'number'
				);
			}
			// quote
			elseif ($entity == $types['quote'])
			{
				$filter[$entity]['OVERDUE'] = array(
					'id' => 'OVERDUE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_OVERDUE_QUOTE'),
					'default' => true,
					'type' => 'list',
					'items' => array(
						'Y' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_YES'),
						'N' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_NO')
					)
				);
				$filter[$entity]['OPPORTUNITY'] = array(
					'id' => 'OPPORTUNITY',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_OPPORTUNITY'),
					'default' => true,
					'type' => 'number'
				);
				$filter[$entity]['CLOSEDATE'] = array(
					'id' => 'CLOSEDATE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_CLOSEDATE_QUOTE'),
					'default' => true,
					'type' => 'date'
				);
			}
			// invoice
			elseif ($entity == $types['invoice'])
			{
				$filter[$entity]['OVERDUE'] = array(
					'id' => 'OVERDUE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_OVERDUE_INVOICE'),
					'default' => true,
					'type' => 'list',
					'items' => array(
						'Y' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_YES'),
						'N' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_NO')
					)
				);
				$filter[$entity]['DATE_PAY_BEFORE'] = array(
					'id' => 'DATE_PAY_BEFORE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_DATE_PAY_INVOICE'),
					'default' => true,
					'type' => 'date'
				);
				$filter[$entity]['PRICE'] = array(
					'id' => 'PRICE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_PRICE'),
					'default' => true,
					'type' => 'number'
				);
			}
			// common
			$assCode = $entity == $types['invoice'] ? 'RESPONSIBLE_ID' : 'ASSIGNED_BY_ID';
			$filter[$entity][$assCode] = array(
				'id' => $assCode,
				'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_ASSIGNED_BY_ID'),
				'default' => true,
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'assigned_by',
						'FIELD_ID' => $assCode
					)
				),
				'params' => array(
					'multiple' => 'Y'
				)
			);
			if ($entity == $types['deal'] || $entity == $types['lead'])
			{
				$activity = array();
				foreach (EntityCounterType::getAll() as $typeID)
				{
					$activity[$typeID] = Loc::getMessage('CRM_KANBAN_HELPER_FLT_ACTIVITY_'.EntityCounterType::resolveName($typeID));
				}
				$filter[$entity]['ACTIVITY_COUNTER'] = array(
					'id' => 'ACTIVITY_COUNTER',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_MY_ACTIVITY'),
					'default' => false,
					'type' => 'list',
					'items' => $activity,
					'params' => array(
						'multiple' => 'Y'
					)
				);
			}
			// add new columns
			if ($entity == $types['deal'] || $entity == $types['lead'])
			{
				$columns = PhaseSemantics::getListFilterInfo(
					$entity == $types['deal']
					? \CCrmOwnerType::Deal
					: \CCrmOwnerType::Lead
				);
			}
			else
			{
				$columns = PhaseSemantics::getListFilterInfo(false, null, true);
			}
			$id = $entity == $types['deal']
					? 'STAGE_SEMANTIC_ID'
					: 'STATUS_SEMANTIC_ID';
			$filter[$entity][$id] = array_merge(
				array(
					'id' => $id,
					'name' => $entity == $types['deal']
								? Loc::getMessage('CRM_KANBAN_HELPER_FLT_STAGE_SEMANTIC_ID')
								: Loc::getMessage('CRM_KANBAN_HELPER_FLT_STATUS_SEMANTIC_ID'),
					'default' => false,
					'params' => array(
						'multiple' => 'Y'
					)
				),
				$columns
			);
		}

		return $filter[$entity];
	}

	/**
	 * Get filter presets for Kanban.
	 * @param string $entity Type of entity.
	 * @return array
	 */
	public static function getPresets($entity)
	{
		static $presets = array();
		$types = self::getTypes();

		if (!array_key_exists($entity, $presets))
		{
			$presets[$entity] = array();
			$uid = \CCrmSecurityHelper::GetCurrentUserID();
			if ($uid)
			{
				if ($uname = \Cuser::getById($uid)->fetch())
				{
					$uname = \CUser::FormatName(\CSite::GetNameFormat(false), $uname);
				}
			}
			// lead
			if ($entity == $types['lead'])
			{
				$presets[$entity]['filter_lpr1_my'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_LPR_MY'),
					'default' => true,
					'fields' => array(
						'ASSIGNED_BY_ID' => $uid,
						'ASSIGNED_BY_ID_name' => $uname,
						'SOURCE_ID' => array(),
						'DATE_CREATE' => '',
						'COMMUNICATION_TYPE' => array()
					)
				);
				$presets[$entity]['filter_lpr2_phone'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_LPR_PHONE'),
					'fields' => array(
						'COMMUNICATION_TYPE' => array(
							\CCrmFieldMulti::PHONE
						),
						'SOURCE_ID' => array(),
						'DATE_CREATE' => '',
						'ASSIGNED_BY_ID' => ''
					)
				);
				$presets[$entity]['filter_lpr3_email'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_LPR_EMAIL'),
					'fields' => array(
						'COMMUNICATION_TYPE' => array(
							\CCrmFieldMulti::EMAIL
						),
						'SOURCE_ID' => array(),
						'DATE_CREATE' => '',
						'ASSIGNED_BY_ID' => ''
					)
				);
			}
			// deal
			elseif ($entity == $types['deal'])
			{
				$presets[$entity]['filter_dpr1_my'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_DPR_MY'),
					'default' => true,
					'fields' => array(
						'ASSIGNED_BY_ID' => $uid,
						'ASSIGNED_BY_ID_name' => $uname,
						'CONTACT_ID' => '',
						'COMPANY_ID' => '',
						'CLOSEDATE' => '',
						'OPPORTUNITY' => ''
					)
				);
			}
			// quote
			elseif ($entity == $types['quote'])
			{
				$presets[$entity]['filter_qt1_my'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_QT_MY'),
					'default' => true,
					'fields' => array(
						'ASSIGNED_BY_ID' => $uid,
						'ASSIGNED_BY_ID_name' => $uname,
						'OVERDUE' => '',
						'CLOSEDATE' => '',
						'OPPORTUNITY' => ''
					)
				);
				$presets[$entity]['filter_qt1_my_overdue'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_QT_MY_OVERDUE'),
					'fields' => array(
						'ASSIGNED_BY_ID' => $uid,
						'ASSIGNED_BY_ID_name' => $uname,
						'OVERDUE' => 'Y',
						'CLOSEDATE' => '',
						'OPPORTUNITY' => ''
					)
				);
				$presets[$entity]['filter_qt1_my_not_overdue'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_QT_MY_NOT_OVERDUE'),
					'fields' => array(
						'ASSIGNED_BY_ID' => $uid,
						'ASSIGNED_BY_ID_name' => $uname,
						'OVERDUE' => 'N',
						'CLOSEDATE' => '',
						'OPPORTUNITY' => ''
					)
				);
			}
			// invoice
			elseif ($entity == $types['invoice'])
			{
				$presets[$entity]['filter_inv1_my'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_INV_MY'),
					'default' => true,
					'fields' => array(
						'RESPONSIBLE_ID' => $uid,
						'RESPONSIBLE_ID_name' => $uname,
						'OVERDUE' => '',
						'DATE_PAY_BEFORE' => '',
						'PRICE' => ''
					)
				);
				$presets[$entity]['filter_inv2_overdue'] = array(
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_INV_OVERDUE'),
					'fields' => array(
						'RESPONSIBLE_ID' => $uid,
						'RESPONSIBLE_ID_name' => $uname,
						'OVERDUE' => 'Y',
						'DATE_PAY_BEFORE' => '',
						'PRICE' => ''
					)
				);
			}
		}

		return $presets[$entity];
	}

	/**
	 * Get default key of filter for Kanban.
	 * @param string $entity Type of entity.
	 * @return array
	 */
	public static function getDefaultFilterKey($entity)
	{
		$keys = array();
		foreach (self::getFilter($entity) as $key => $item)
		{
			$keys[$key] = $item['default']===true;
		}
		return $keys;
	}
}