<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Thurly\Crm\Automation\Engine\Template;
use Thurly\Crm\Automation\Factory;
use Thurly\Crm\Settings\LeadSettings;
use Thurly\Main;
use Thurly\Main\Localization\Loc;
use Thurly\Crm\Category\DealCategory;

Loc::loadMessages(__FILE__);

class CrmAutomationComponent extends \CThurlyComponent
{
	protected $entity;

	protected function getEntityTypeId()
	{
		return isset($this->arParams['ENTITY_TYPE_ID']) ? (int)$this->arParams['ENTITY_TYPE_ID'] : 0;
	}

	protected function getEntityCategoryId()
	{
		return isset($this->arParams['ENTITY_CATEGORY_ID']) ? (int)$this->arParams['ENTITY_CATEGORY_ID'] : null;
	}

	protected function getEntityId()
	{
		return isset($this->arParams['ENTITY_ID']) ? (int)$this->arParams['ENTITY_ID'] : 0;
	}

	protected function getEntity()
	{
		if ($this->entity === null)
		{
			if ($this->getEntityTypeId() === \CCrmOwnerType::Deal)
			{
				$this->entity = \CCrmDeal::GetByID($this->getEntityId());
			}
			elseif ($this->getEntityTypeId() === \CCrmOwnerType::Lead)
			{
				$this->entity = \CCrmLead::GetByID($this->getEntityId());
			}
		}

		return $this->entity;
	}

	protected function getEntityStatus()
	{
		$status = '';
		$entity = $this->getEntity();

		if (!$entity)
			return $status;

		if ($this->getEntityTypeId() === \CCrmOwnerType::Deal)
		{
			$status = $entity['STAGE_ID'];
		}
		elseif ($this->getEntityTypeId() === \CCrmOwnerType::Lead)
		{
			$status = $entity['STATUS_ID'];
		}

		return $status;
	}

	protected function getEntityStatuses($entityCategoryId)
	{
		$statuses = array();

		$processColor = \CCrmViewHelper::PROCESS_COLOR;
		$successColor = \CCrmViewHelper::SUCCESS_COLOR;
		$failureColor = \CCrmViewHelper::FAILURE_COLOR;

		if ($this->getEntityTypeId() === \CCrmOwnerType::Deal)
		{
			$statuses = \CCrmViewHelper::GetDealStageInfos($entityCategoryId);

			foreach ($statuses as $id => $stageInfo)
			{
				if (!empty($stageInfo['COLOR']))
					continue;

				$stageSemanticID = CCrmDeal::GetSemanticID($stageInfo['STATUS_ID'], $entityCategoryId);
				$isSuccess = $stageSemanticID === Thurly\Crm\PhaseSemantics::SUCCESS;
				$isFailure = $stageSemanticID === Thurly\Crm\PhaseSemantics::FAILURE;

				$statuses[$id]['COLOR'] = ($isSuccess ? $successColor : ($isFailure ? $failureColor : $processColor));
			}
		}
		elseif ($this->getEntityTypeId() === \CCrmOwnerType::Lead)
		{
			$statuses = \CCrmViewHelper::GetLeadStatusInfos();

			foreach ($statuses as $id => $statusInfo)
			{
				if (!empty($statusInfo['COLOR']))
					continue;

				$semanticId = \CAllCrmLead::GetSemanticID($statusInfo["STATUS_ID"]);

				if ($semanticId == \Thurly\Crm\PhaseSemantics::PROCESS)
					$statuses[$id]["COLOR"] = $processColor;
				else if ($semanticId == \Thurly\Crm\PhaseSemantics::FAILURE)
					$statuses[$id]["COLOR"] = $failureColor;
				else if ($semanticId == \Thurly\Crm\PhaseSemantics::SUCCESS)
					$statuses[$id]["COLOR"] = $successColor;
			}
		}

		return $statuses;
	}

	protected function getTemplates(array $statuses, array $availableRobots)
	{
		$relation = array();

		$documentType = array(
			'crm',
			\CCrmBizProcHelper::ResolveDocumentName($this->getEntityTypeId()),
			\CCrmOwnerType::ResolveName($this->getEntityTypeId())
		);


		$iterator = \Thurly\Crm\Automation\Engine\Entity\TemplateTable::getList(array(
			'filter' => array(
				'=ENTITY_TYPE_ID' => $this->getEntityTypeId(),
				'@ENTITY_STATUS' => $statuses
			)
		));

		while ($row = $iterator->fetch())
		{
			$template = new Template($row);
			$templateArray = $template->toArray();
			foreach ($templateArray['ROBOTS'] as $i => $robot)
			{
				$templateArray['ROBOTS'][$i]['viewData'] = static::getRobotViewData($robot, $availableRobots, $documentType);
			}

			$relation[$row['ENTITY_STATUS']] = $templateArray;
		}

		foreach ($statuses as $status)
		{
			if (!isset($relation[$status]))
			{
				$template = new Template(array(
					'ENTITY_TYPE_ID' => $this->getEntityTypeId(),
					'ENTITY_STATUS' => $status,
				));
				$template->save(array(), 1); // save bizproc template
				$relation[$status] = $template->toArray();
			}
		}

		return array_values($relation);
	}

	public function getRobotViewData($robot, array $availableRobots, array $documentType)
	{
		$result = array(
			'responsibleLabel' => '',
			'responsibleUrl' => '',
			'responsibleId' => 0,
		);

		$type = strtolower($robot['Type']);
		if (isset($availableRobots[$type]) && isset($availableRobots[$type]['ROBOT_SETTINGS']))
		{
			$settings = $availableRobots[$type]['ROBOT_SETTINGS'];

			if ($settings['RESPONSIBLE_TO_HEAD'] && $robot['Properties'][$settings['RESPONSIBLE_TO_HEAD']] == 'Y')
			{
				$result['responsibleLabel'] = Loc::getMessage('CRM_AUTOMATION_TO_HEAD');
			}

			if (isset($settings['RESPONSIBLE_PROPERTY']))
			{
				$users = (array)$robot['Properties'][$settings['RESPONSIBLE_PROPERTY']];
				$usersLabel = CBPHelper::UsersArrayToString(
					$users,
					array(),
					$documentType,
					false
				);

				if ($result['responsibleLabel'] && $usersLabel)
					$result['responsibleLabel'] .= ', ';
				$result['responsibleLabel'] .= $usersLabel;

				$user = $users[0];
				if (count($users) == 1 && $user && strpos($user, 'user_') === 0)
				{
					$id = (int)substr($user, 5);
					$result['responsibleUrl'] = CComponentEngine::MakePathFromTemplate(
						'/company/personal/user/#user_id#/',
						array('user_id' => $id)
					);
					$result['responsibleId'] = $id;
				}
			}
		}
		return $result;
	}

	protected function getTriggers(array $statuses)
	{
		$iterator = \Thurly\Crm\Automation\Trigger\Entity\TriggerTable::getList(array(
			'filter' => array(
				'=ENTITY_TYPE_ID' => $this->getEntityTypeId(),
				'@ENTITY_STATUS' => $statuses
			)
		));

		return $iterator->fetchAll();
	}

	public function executeComponent()
	{
		if (!Main\Loader::includeModule('crm'))
		{
			ShowError(Loc::getMessage('CRM_MODULE_NOT_INSTALLED'));
			return;
		}

		if (!Main\Loader::includeModule('bizproc'))
		{
			ShowError(Loc::getMessage('BIZPROC_MODULE_NOT_INSTALLED'));
			return;
		}

		//for HTML editor
		Main\Loader::includeModule('fileman');

		$entityTypeId = $this->getEntityTypeId();
		$entityCategoryId = $this->getEntityCategoryId();

		if (!Factory::isSupported($entityTypeId))
		{
			ShowError(Loc::getMessage('CRM_AUTOMATION_NOT_SUPPORTED'));
			return;
		}

		if (!Factory::isAutomationAvailable($entityTypeId))
		{
			ShowError(Loc::getMessage('CRM_AUTOMATION_NOT_AVAILABLE'));
			return;
		}

		if ($entityTypeId === CCrmOwnerType::Lead && !LeadSettings::isEnabled())
		{
			ShowError(Loc::getMessage('CRM_AUTOMATION_NOT_AVAILABLE_SIMPLE_CRM'));
			return;
		}

		$permissions = \CCrmPerms::GetCurrentUserPermissions();
		$canEdit = $permissions->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE');
		$entityId = $this->getEntityId();

		if (!$entityId && !$canEdit)
		{
			ShowError(Loc::getMessage('CRM_AUTOMATION_ACCESS_DENIED'));
			return;
		}

		$entity = $this->getEntity();
		if (!$entity && $entityId > 0)
		{
			ShowError(Loc::getMessage('CRM_AUTOMATION_ACCESS_DENIED'));
			return;
		}

		if (isset($this->arParams['ACTION']) && $this->arParams['ACTION'] == 'ROBOT_SETTINGS')
		{
			$template = new \Thurly\Crm\Automation\Engine\Template(array(
				'ENTITY_TYPE_ID' => $this->getEntityTypeId()
			));

			$dialog = $template->getRobotSettingsDialog($this->arParams['~ROBOT_DATA'], $this->arParams['~REQUEST']);

			if ($dialog === '')
				return;

			if (!($dialog instanceof \Thurly\Bizproc\Activity\PropertiesDialog))
			{
				ShowError('Robot dialog not supported in current context.');
				return;
			}

			if (is_array($this->arParams['~CONTEXT']))
				$dialog->setContext($this->arParams['~CONTEXT']);

			if (strpos($this->arParams['~ROBOT_DATA']['Type'], 'rest_') === 0)
			{
				$this->arResult = array('dialog' => $dialog);
				$this->includeComponentTemplate('rest_robot_properties_dialog');
				return;
			}
			elseif (strtolower($this->arParams['~ROBOT_DATA']['Type']) === 'setfieldactivity')
			{
				$this->arResult = array('dialog' => $dialog);
				$this->includeComponentTemplate('setfield_robot_properties_dialog');
				return;
			}

			$dialog->setDialogFileName('robot_properties_dialog');
			echo $dialog;
			return;
		}

		$entityStatuses = $this->getEntityStatuses($entityCategoryId);

		$log = array();
		if ($entity)
		{
			$tracker = new \Thurly\Crm\Automation\Tracker($entityTypeId, $entityId);
			$log = $tracker->getLog();
		}

		$availableRobots = Template::getAvailableRobots($entityTypeId);

		$this->arResult = array(
			'CAN_EDIT' => $canEdit,
			'ENTITY' => $entity,
			'ENTITY_STATUS' => $this->getEntityStatus(),
			'ENTITY_TYPE_ID' => $entityTypeId,
			'ENTITY_TYPE_NAME' => \CCrmOwnerType::ResolveName($entityTypeId),
			'ENTITY_ID' => $entityId,
			'ENTITY_CATEGORY_ID' => $entityCategoryId,
			'STATUSES' => $entityStatuses,
			'TEMPLATES' => $this->getTemplates(array_keys($entityStatuses), $availableRobots),
			'TRIGGERS' => $this->getTriggers(array_keys($entityStatuses)),
			'AVAILABLE_TRIGGERS' => Factory::getAvailableTriggers($entityTypeId),
			'BIZPROC_EDITOR_URL' => $this->getBpDesignerEditUrl($entityTypeId),
			'AVAILABLE_ROBOTS' => array_values($availableRobots),
			'ENTITY_FIELDS' => $this->getEntityFields(),
			'LOG' => $log,
			'STATUSES_EDIT_URL' => $this->getStatusesEditUrl($entityTypeId, $entityCategoryId),
			'B24_TARIF_ZONE' => SITE_ID,
			'USER_OPTIONS' => array(
				'defaults' => \CUserOptions::GetOption('crm.automation', 'defaults', array()),
				'save_state_checkboxes' => \CUserOptions::GetOption('crm.automation', 'save_state_checkboxes', array())
			),
			'FRAME_MODE' => $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER',
			'USE_DISK' => Main\Loader::includeModule('disk')
		);

		if (IsModuleInstalled('thurlyos') && CModule::IncludeModule('thurlyos'))
		{
			$this->arResult['B24_TARIF_ZONE'] = \CThurlyOS::getLicensePrefix();
		}

		$this->includeComponentTemplate();
	}

	public static function getDestinationData($entityTypeId)
	{
		$result = array('LAST' => array());

		if (!Main\Loader::includeModule('socialnetwork'))
			return array();

		$arStructure = CSocNetLogDestination::GetStucture(array());
		$result['DEPARTMENT'] = $arStructure['department'];
		$result['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$result['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		$result['DEST_SORT'] = CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "CRM_AUTOMATION",
		));

		CSocNetLogDestination::fillLastDestination(
			$result['DEST_SORT'],
			$result['LAST']
		);

		$destUser = array();
		foreach ($result["LAST"]["USERS"] as $value)
		{
			$destUser[] = str_replace("U", "", $value);
		}

		$result["USERS"] = \CSocNetLogDestination::getUsers(array("id" => $destUser));
		$result["ROLES"] = array();

		$documentUserFields = \Thurly\Crm\Automation\Helper::getDocumentFields(array(
			'crm',
			\CCrmBizProcHelper::ResolveDocumentName($entityTypeId),
			\CCrmOwnerType::ResolveName($entityTypeId)
		), 'user');

		foreach ($documentUserFields as $field)
		{
			$result["ROLES"]['BPR_'.$field['Id']] = array(
				'id' => 'BPR_'.$field['Id'],
				'entityId' => $field['Expression'],
				'name' => $field['Name'],
				'avatar' => '',
				'desc' => '&nbsp;'
			);
		}

		$result["LAST"]["USERS"]["ROLES"] = array();

		return $result;
	}

	public static function getWebhookHandler($userId, $entityTypeId)
	{
		if (!Main\Loader::includeModule('rest'))
			return '';

		$passwd = \Thurly\Crm\Automation\Trigger\WebHookTrigger::generatePassword($userId);
		if ($passwd)
		{
			$passwd['HANDLER'] = SITE_DIR.'rest/'.$userId.'/'.$passwd['PASSWORD'].'/crm.automation.trigger/?target='.\CCrmOwnerType::ResolveName($entityTypeId).'_{{ID}}';
			return $passwd;
		}

		return array();
	}

	private function getBpDesignerEditUrl($entityTypeId)
	{
		if (!Factory::canUseBizprocDesigner())
			return '';

		$siteDir = isset($this->arParams['SITE_DIR']) ? (string)$this->arParams['SITE_DIR'] : SITE_DIR;
		$siteDir = rtrim($siteDir, '/');
		$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeId);

		$url = "{$siteDir}/crm/configs/bp/CRM_{$entityTypeName}/edit/#ID#/";

		if (!empty($this->arParams['back_url']))
		{
			$url .= '?back_url='.urlencode($this->arParams['back_url']);
		}

		return $url;
	}

	private function getStatusesEditUrl($entityTypeId, $categoryId)
	{
		$statusId = '';

		switch ($entityTypeId)
		{
			case CCrmOwnerType::Deal:
				$statusId = DealCategory::getStatusEntityID($categoryId);
				break;
			case CCrmOwnerType::Lead:
				$statusId = 'STATUS';
				break;
		}

		return CComponentEngine::MakePathFromTemplate(
			SITE_DIR.'crm/configs/status/?ACTIVE_TAB=status_tab_#status_id#',
			array('status_id' => $statusId)
		);
	}

	private function getEntityFields($filter = null)
	{
		return array_values(\Thurly\Crm\Automation\Helper::getDocumentFields(array(
			'crm',
			\CCrmBizProcHelper::ResolveDocumentName($this->getEntityTypeId()),
			\CCrmOwnerType::ResolveName($this->getEntityTypeId())
		), $filter));
	}
}