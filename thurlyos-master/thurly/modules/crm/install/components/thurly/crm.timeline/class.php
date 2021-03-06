<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main;
use Thurly\Main\Type\DateTime;
use Thurly\Main\Localization\Loc;
use Thurly\Main\DB\SqlExpression;
use Thurly\Main\Entity\Base;
use Thurly\Main\Entity\Query;
use Thurly\Main\Entity\ReferenceField;
use Thurly\Crm;
use Thurly\Crm\Timeline\TimelineType;
use Thurly\Crm\Timeline\Entity\TimelineTable;
use Thurly\Crm\Timeline\Entity\TimelineBindingTable;
use Thurly\Crm\Timeline\ActivityController;
use Thurly\Crm\Timeline\TimelineEntry;

Loc::loadMessages(__FILE__);

class CCrmTimelineComponent extends CThurlyComponent
{
	/** @var int */
	protected $userID = 0;
	/** @var  CCrmPerms|null */
	protected $userPermissions = null;
	/** @var string */
	protected $guid = '';
	/** @var string */
	protected $entityTypeName = '';
	/** @var int */
	protected $entityTypeID = \CCrmOwnerType::Undefined;
	/** @var int */
	protected $entityID = 0;
	/** @var array|null  */
	private $enityInfo = null;
	/** @var array */
	protected $errors = array();
	/** @var CTextParser|null  */
	protected $parser = null;
	/** @var string */
	protected $pullTagName = '';

	public function getEntityTypeID()
	{
		return $this->entityTypeID;
	}

	public function setEntityTypeID($entityTypeID)
	{
		$this->entityTypeID = $entityTypeID;
	}

	public function getEntityID()
	{
		return $this->entityID;
	}

	public function setEntityID($entityID)
	{
		$this->entityID = $entityID;
	}

	public function executeComponent()
	{
		$this->initialize();
		if (!empty($this->errors))
			return;
		$this->includeComponentTemplate();
	}
	protected function initialize()
	{
		global $APPLICATION;

		if(!Main\Loader::includeModule('crm'))
		{
			$this->errors[] = GetMessage('CRM_MODULE_NOT_INSTALLED');
			return;
		}

		$this->userID = CCrmSecurityHelper::GetCurrentUserID();
		$this->userPermissions = CCrmPerms::GetCurrentUserPermissions();
		$this->guid = $this->arResult['GUID'] = isset($this->arParams['~GUID']) ? $this->arParams['~GUID'] : 'timeline';

		$entityTypeName = isset($this->arParams['~ENTITY_TYPE_NAME']) ? $this->arParams['~ENTITY_TYPE_NAME'] : '';
		$entityTypeID = isset($this->arParams['~ENTITY_TYPE_ID']) ? (int)$this->arParams['~ENTITY_TYPE_ID'] : 0;
		if($entityTypeName !== '')
		{
			$entityTypeID = CCrmOwnerType::ResolveID($entityTypeName);
		}
		else if($entityTypeID > 0)
		{
			$entityTypeName = CCrmOwnerType::ResolveName($entityTypeID);
		}

		$this->entityTypeName = $entityTypeName;
		$this->entityTypeID = $entityTypeID;

		if(!\CCrmOwnerType::IsDefined($this->entityTypeID))
		{
			$this->errors[] = GetMessage('CRM_TIMELINE_ENTITY_TYPE_NOT_ASSIGNED');
			return;
		}

		$this->entityID = isset($this->arParams['ENTITY_ID']) ? (int)$this->arParams['ENTITY_ID'] : 0;

		$this->enityInfo = isset($this->arParams['~ENTITY_INFO']) && is_array($this->arParams['~ENTITY_INFO'])
			? $this->arParams['~ENTITY_INFO'] : array();

		if($this->entityID > 0 && !\Thurly\Crm\Security\EntityAuthorization::checkReadPermission($this->entityTypeID, $this->entityID))
		{
			$this->errors[] = GetMessage('CRM_PERMISSION_DENIED');
			return;
		}

		$this->arResult['ACTIVITY_EDITOR_ID'] = isset($this->arParams['~ACTIVITY_EDITOR_ID']) ? $this->arParams['~ACTIVITY_EDITOR_ID'] : '';
		$this->arResult['ENABLE_WAIT'] = isset($this->arParams['~ENABLE_WAIT']) ? (bool)$this->arParams['~ENABLE_WAIT'] : false;
		$this->arResult['WAIT_TARGET_DATES'] = isset($this->arParams['~WAIT_TARGET_DATES']) && is_array($this->arParams['~WAIT_TARGET_DATES'])
			? $this->arParams['~WAIT_TARGET_DATES'] : array();
		$this->arResult['WAIT_CONFIG'] = \CUserOptions::GetOption(
			'crm.timeline.wait',
			strtolower($this->guid),
			array()
		);

		if(!Crm\Integration\SmsManager::canUse())
		{
			$this->arResult['ENABLE_SMS'] = false;
		}
		else
		{
			$this->arResult['ENABLE_SMS'] = isset($this->arParams['~ENABLE_SMS']) ? (bool)$this->arParams['~ENABLE_SMS'] : true;
		}

		$this->arResult['SMS_MANAGE_URL'] = \Thurly\Crm\Integration\SmsManager::getManageUrl();
		$this->arResult['SMS_CAN_SEND_MESSAGE'] = \Thurly\Crm\Integration\SmsManager::canSendMessage();
		$this->arResult['SMS_STATUS_DESCRIPTIONS'] = \Thurly\Crm\Integration\SmsManager::getMessageStatusDescriptions();
		$this->arResult['SMS_STATUS_SEMANTICS'] = \Thurly\Crm\Integration\SmsManager::getMessageStatusSemantics();
		$this->arResult['SMS_CONFIG'] = \Thurly\Crm\Integration\SmsManager::getEditorConfig(
			$this->entityTypeID,
			$this->entityID
		);

		if(!Main\ModuleManager::isModuleInstalled('calendar'))
		{
			$this->arResult['ENABLE_CALL'] = $this->arResult['ENABLE_MEETING'] = false;
		}
		else
		{
			$this->arResult['ENABLE_CALL'] = isset($this->arParams['~ENABLE_CALL']) ? (bool)$this->arParams['~ENABLE_CALL'] : true;
			$this->arResult['ENABLE_MEETING'] = isset($this->arParams['~ENABLE_MEETING']) ? (bool)$this->arParams['~ENABLE_MEETING'] : true;
		}

		if(!Crm\Activity\Provider\Visit::isAvailable())
		{
			$this->arResult['ENABLE_VISIT'] = false;
		}
		else
		{
			$this->arResult['ENABLE_VISIT'] = isset($this->arParams['~ENABLE_VISIT']) ? (bool)$this->arParams['~ENABLE_VISIT'] : true;
			$this->arResult['VISIT_PARAMETERS'] = Crm\Activity\Provider\Visit::getPopupParameters();
		}

		$this->arResult['ADDITIONAL_TABS'] = array();
		$this->arResult['ENABLE_REST'] = false;
		if(Main\Loader::includeModule('rest'))
		{
			$this->arResult['ENABLE_REST'] = true;
			\CJSCore::Init(array('marketplace'));

			$this->arResult['REST_PLACEMENT'] = 'CRM_'.$this->entityTypeName.'_DETAIL_ACTIVITY';
			$placementHandlerList = \Thurly\Rest\PlacementTable::getHandlersList($this->arResult['REST_PLACEMENT']);

			if(count($placementHandlerList) > 0)
			{
				\CJSCore::Init(array('applayout'));

				foreach($placementHandlerList as $placementHandler)
				{
					$this->arResult['ADDITIONAL_TABS'][] = array(
						'id' => 'activity_rest_'.$placementHandler['APP_ID'].'_'.$placementHandler['ID'],
						'name' => strlen($placementHandler['TITLE']) > 0
							? $placementHandler['TITLE']
							: $placementHandler['APP_NAME'],
					);
				}
			}

			$this->arResult['ADDITIONAL_TABS'][] = array(
				'id' => 'activity_rest_applist',
				'name' => Loc::getMessage('CRM_REST_BUTTON_TITLE')
			);
		}

		$this->arResult['ENABLE_EMAIL'] = isset($this->arParams['~ENABLE_EMAIL']) ? (bool)$this->arParams['~ENABLE_EMAIL'] : true;
		$this->arResult['ENABLE_TASK'] = isset($this->arParams['~ENABLE_TASK']) ? (bool)$this->arParams['~ENABLE_TASK'] : true;

		$this->arResult['PROGRESS_SEMANTICS'] = isset($this->arParams['~PROGRESS_SEMANTICS']) ? $this->arParams['~PROGRESS_SEMANTICS'] : '';

		$this->arResult['CURRENT_URL'] = $APPLICATION->GetCurPageParam('', array('bxajaxid', 'AJAX_CALL'));
		$this->arResult['AJAX_ID'] = isset($this->arParams['AJAX_ID']) ? $this->arParams['AJAX_ID'] : '';
		$this->arResult['PATH_TO_USER_PROFILE'] = CrmCheckPath('PATH_TO_USER_PROFILE', $this->arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');

		$this->arResult['ENTITY_TYPE_ID'] = $this->entityTypeID;
		$this->arResult['ENTITY_TYPE_NAME'] = $this->entityTypeName;
		$this->arResult['ENTITY_ID'] = $this->entityID;
		$this->arResult['ENTITY_INFO'] = $this->enityInfo;

		$this->parser = new CTextParser();
		$this->parser->allow['SMILES'] = 'N';

		$this->arResult['READ_ONLY'] = isset($this->arParams['~READ_ONLY']) && $this->arParams['~READ_ONLY'] === true ;

		$this->prepareScheduleItems();
		$this->arResult['HISTORY_ITEMS'] = $this->prepareHistoryItems(null, 10);

		$this->arResult['FIXED_ITEMS'] = $this->prepareHistoryItems(null, 3, true);

		if(Thurly\Main\Loader::includeModule('pull'))
		{
			$this->pullTagName = $this->arResult['PULL_TAG_NAME'] = TimelineEntry::prepareEntityPushTag($this->entityTypeID, $this->entityID);
			\CPullWatch::Add($this->userID, $this->pullTagName);

			if ($this->arResult['ENABLE_SMS'])
			{
				\CPullWatch::Add($this->userID, 'MESSAGESERVICE');
			}
		}
	}
	protected function prepareScheduleItems()
	{
		if($this->entityID <= 0)
		{
			return ($this->arResult['SCHEDULE_ITEMS'] = array());
		}

		$filter = array('STATUS' => CCrmActivityStatus::Waiting);
		$filter['BINDINGS'] = array(
			array('OWNER_TYPE_ID' => $this->entityTypeID, 'OWNER_ID' => $this->entityID)
		);

		$dbResult = \CCrmActivity::GetList(
			array('DEADLINE' => 'ASC'),
			$filter,
			false,
			false,
			array(
				'ID', 'OWNER_ID', 'OWNER_TYPE_ID',
				'TYPE_ID', 'PROVIDER_ID', 'PROVIDER_TYPE_ID', 'ASSOCIATED_ENTITY_ID', 'DIRECTION',
				'SUBJECT', 'STATUS', 'DESCRIPTION', 'DESCRIPTION_TYPE',
				'DEADLINE', 'RESPONSIBLE_ID'
			),
			array('QUERY_OPTIONS' => array('LIMIT' => 100, 'OFFSET' => 0))
		);

		$items = array();
		while($fields = $dbResult->Fetch())
		{
			$items[$fields['ID']] = ActivityController::prepareScheduleDataModel($fields);
		}

		\Thurly\Crm\Timeline\EntityController::prepareAuthorInfoBulk($items);

		$communications = \CCrmActivity::PrepareCommunicationInfos(array_keys($items));
		foreach($communications as $ID => $info)
		{
			$items[$ID]['ASSOCIATED_ENTITY']['COMMUNICATION'] = $info;
		}

		\Thurly\Crm\Timeline\EntityController::prepareMultiFieldInfoBulk($items);

		$fields = \Thurly\Crm\Pseudoactivity\WaitEntry::getRecentByOwner($this->entityTypeID, $this->entityID);
		if(is_array($fields))
		{
			$items[$fields['ID']] = \Thurly\Crm\Timeline\WaitController::prepareScheduleDataModel(
				$fields,
				array('ENABLE_USER_INFO' => true)
			);
		}

		return ($this->arResult['SCHEDULE_ITEMS'] = array_values($items));
	}
	public function prepareHistoryItems($lastItemTime, $limit, $onlyFixed = false)
	{
		if($this->entityID <= 0)
		{
			return array();
		}

		//Permissions are already checked
		$query = new Query(TimelineTable::getEntity());
		$query->addSelect('*');

		$subQuery = new Query(TimelineBindingTable::getEntity());
		$subQuery->addSelect('OWNER_ID');
		$subQuery->addFilter('=ENTITY_TYPE_ID', $this->entityTypeID);
		$subQuery->addFilter('=ENTITY_ID', $this->entityID);

		if ($onlyFixed)
		{
			$subQuery->addFilter('=IS_FIXED', 'Y');
		}

		$subQuery->addSelect('IS_FIXED');
		$query->addSelect('bind.IS_FIXED', 'IS_FIXED');

		$query->registerRuntimeField('',
			new ReferenceField('bind',
				Base::getInstanceByQuery($subQuery),
				array('=this.ID' => 'ref.OWNER_ID'),
				array('join_type' => 'INNER')
			)
		);

		if($lastItemTime instanceof DateTime)
		{
			//Using '<=' insted of '<' for prevention of loss of items that have same cteation time
			$query->addFilter('<=CREATED', $lastItemTime);
		}

		$query->setOrder(array('CREATED' => 'DESC', 'ID' => 'DESC'));
		if($limit > 0)
		{
			$query->setLimit($limit);
		}

		$dbResult = $query->exec();

		$items = array();
		while($fields = $dbResult->fetch())
		{
			$items[$fields['ID']] = $fields;
		}

		\Thurly\Crm\Timeline\TimelineManager::prepareDisplayData($items);
		return array_values($items);
	}
}