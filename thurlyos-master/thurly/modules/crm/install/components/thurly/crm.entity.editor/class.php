<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CCrmEntityEditorComponent extends CThurlyComponent
{
	/** @var int */
	protected $userID = 0;
	/** @var int */
	protected $entityTypeID = 0;
	/** @var int */
	protected $entityID = 0;
	/** @var string */
	protected $guid = '';
	/** @var string */
	protected $configID = '';
	/** @var array */
	protected $errors = array();

	public function executeComponent()
	{
		$this->initialize();
		$this->includeComponentTemplate();
	}

	protected function initialize()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(!Thurly\Main\Loader::includeModule('crm'))
		{
			$this->errors[] = GetMessage('CRM_MODULE_NOT_INSTALLED');
			return;
		}

		$this->userID = CCrmSecurityHelper::GetCurrentUserID();
		$this->guid = $this->arResult['GUID'] = isset($this->arParams['GUID']) ? $this->arParams['GUID'] : 'entity_editor';
		$this->configID = $this->arResult['CONFIG_ID'] = isset($this->arParams['CONFIG_ID']) ? $this->arParams['CONFIG_ID'] : $this->guid;

		$this->arResult['READ_ONLY'] = isset($this->arParams['~READ_ONLY'])
			&& $this->arParams['~READ_ONLY'];

		$this->arResult['INITIAL_MODE'] = isset($this->arParams['~INITIAL_MODE'])
			? $this->arParams['~INITIAL_MODE'] : '';

		$this->arResult['ENABLE_MODE_TOGGLE'] = !isset($this->arParams['~ENABLE_MODE_TOGGLE'])
			|| $this->arParams['~ENABLE_MODE_TOGGLE'];

		$this->entityTypeID = isset($this->arParams['ENTITY_TYPE_ID'])
			? (int)$this->arParams['ENTITY_TYPE_ID'] : CCrmOwnerType::Undefined;
		$this->entityID = isset($this->arParams['ENTITY_ID'])
			? (int)$this->arParams['ENTITY_ID'] : 0;

		$this->arResult['ENTITY_TYPE_ID'] = $this->entityTypeID;
		$this->arResult['ENTITY_ID'] = $this->entityID;

		$this->arResult['ENTITY_DATA'] = isset($this->arParams['~ENTITY_DATA']) && is_array($this->arParams['~ENTITY_DATA'])
			? $this->arParams['~ENTITY_DATA'] : array();

		$this->arResult['ENTITY_FIELDS'] = isset($this->arParams['~ENTITY_FIELDS']) && is_array($this->arParams['~ENTITY_FIELDS'])
			? $this->arParams['~ENTITY_FIELDS'] : array();

		$this->arResult['ENTITY_VALIDATORS'] = isset($this->arParams['~ENTITY_VALIDATORS']) && is_array($this->arParams['~ENTITY_VALIDATORS'])
			? $this->arParams['~ENTITY_VALIDATORS'] : array();

		$config = CUserOptions::GetOption('crm.entity.editor', $this->configID, null);

		if(!is_array($config) || empty($config))
		{
			$config = isset($this->arParams['~ENTITY_CONFIG']) && is_array($this->arParams['~ENTITY_CONFIG'])
				? $this->arParams['~ENTITY_CONFIG'] : array();
		}

		$this->arResult['ENTITY_CONTROLLERS'] = isset($this->arParams['~ENTITY_CONTROLLERS']) && is_array($this->arParams['~ENTITY_CONTROLLERS'])
				? $this->arParams['~ENTITY_CONTROLLERS'] : array();

		$availableFields = array();

		$requiredFields = array();
		$hasEmptyRequiredFields = false;
		foreach($this->arResult['ENTITY_FIELDS'] as $field)
		{
			$name = isset($field['name']) ? $field['name'] : '';
			if($name === '')
			{
				continue;
			}

			$availableFields[$name] = $field;
			if(isset($field['required']) && $field['required'] === true)
			{
				$requiredFields[$name] = $field;
				if($hasEmptyRequiredFields)
				{
					continue;
				}

				//HACK: Skip if user field of type Boolean. Absence of value is treated as equivalent to FALSE.
				$fieldType = isset($field['type']) ? $field['type'] : '';
				if($fieldType === 'userField')
				{
					$fieldInfo = isset($field['data']) && isset($field['data']['fieldInfo'])
						? $field['data']['fieldInfo'] : array();

					if(isset($fieldInfo['USER_TYPE_ID']) && $fieldInfo['USER_TYPE_ID'] === 'boolean')
					{
						continue;
					}
				}

				if(isset($this->arResult['ENTITY_DATA'][$name])
					&& is_array($this->arResult['ENTITY_DATA'][$name])
					&& isset($this->arResult['ENTITY_DATA'][$name]['IS_EMPTY'])
					&& $this->arResult['ENTITY_DATA'][$name]['IS_EMPTY']
				)
				{
					$hasEmptyRequiredFields = true;
				}
			}
		}

		$primarySectionIndex = 0;
		$serviceSectionIndex = -1;
		$scheme = array();
		for($i = 0, $configQty = count($config); $i < $configQty; $i++)
		{
			$configItem = $config[$i];
			$type = isset($configItem['type']) ? $configItem['type'] : '';
			if($type !== 'section')
			{
				continue;
			}

			$sectionName = isset($configItem['name']) ? $configItem['name'] : '';
			if($sectionName === 'main')
			{
				$primarySectionIndex = $i;
			}
			elseif($sectionName === 'required')
			{
				$serviceSectionIndex = $i;
			}

			$elements = isset($configItem['elements']) && is_array($configItem['elements'])
				? $configItem['elements'] : array();

			$schemeElements = array();
			for($j = 0, $elementQty = count($elements); $j < $elementQty; $j++)
			{
				$configElement = $elements[$j];
				$name = isset($configElement['name']) ? $configElement['name'] : '';
				if($name === '')
				{
					continue;
				}

				$schemeElement = $availableFields[$name];
				$title = isset($configElement['title']) ? $configElement['title'] : '';
				if($title !== '')
				{
					if(isset($schemeElement['title']))
					{
						$schemeElement['originalTitle'] = $schemeElement['title'];
					}
					$schemeElement['title'] = $title;
				}

				$schemeElements[] = $schemeElement;
				unset($availableFields[$name]);

				if(isset($requiredFields[$name]))
				{
					unset($requiredFields[$name]);
				}
			}
			$scheme[] = array_merge($configItem, array('elements' => $schemeElements));
		}

		//Add section 'Required Fields'
		if(!empty($requiredFields) && !$this->arResult['READ_ONLY'])
		{
			$schemeElements = array();
			if($serviceSectionIndex >= 0)
			{
				$configItem = $config[$serviceSectionIndex];
				if(isset($scheme[$serviceSectionIndex]['elements'])
					&& is_array($scheme[$serviceSectionIndex]['elements'])
				)
				{
					$schemeElements = $scheme[$serviceSectionIndex]['elements'];
				}
			}
			else
			{
				$configItem = array(
					'name' => 'required',
					'title' => Loc::getMessage('CRM_ENTITY_ED_REQUIRED_FIELD_SECTION'),
					'type' => 'section',
					'elements' => array()
				);

				$serviceSectionIndex = $primarySectionIndex + 1;
				array_splice(
					$config,
					$serviceSectionIndex,
					0,
					array($configItem)
				);

				array_splice(
					$scheme,
					$serviceSectionIndex,
					0,
					array(array_merge($configItem, array('elements' => array())))
				);
			}

			foreach($requiredFields as $fieldName => $fieldInfo)
			{
				$configItem['elements'][] = array('name' => $fieldName);
				$schemeElements[] = $fieldInfo;
			}

			$scheme[$serviceSectionIndex]['elements'] = $schemeElements;

			//Force Edit mode if empty required fields are found.
			if($hasEmptyRequiredFields)
			{
				$this->arResult['INITIAL_MODE'] = 'edit';
			}
		}

		$this->arResult['ENTITY_CONFIG'] = $config;
		$this->arResult['ENTITY_SCHEME'] = $scheme;
		$this->arResult['ENTITY_AVAILABLE_FIELDS'] = array_values($availableFields);

		$this->arResult['ENABLE_AJAX_FORM'] = !isset($this->arParams['~ENABLE_AJAX_FORM'])
			|| $this->arParams['~ENABLE_AJAX_FORM'];

		$this->arResult['ENABLE_AJAX_FORM'] = !isset($this->arParams['~ENABLE_AJAX_FORM'])
			|| $this->arParams['~ENABLE_AJAX_FORM'];

		$this->arResult['ENABLE_USER_FIELD_CREATION'] = isset($this->arParams['~ENABLE_USER_FIELD_CREATION'])
			&& $this->arParams['~ENABLE_USER_FIELD_CREATION'];
		$this->arResult['USER_FIELD_ENTITY_ID'] = isset($this->arParams['~USER_FIELD_ENTITY_ID'])
			? $this->arParams['~USER_FIELD_ENTITY_ID'] : '';
		$this->arResult['USER_FIELD_CREATE_PAGE_URL'] = isset($this->arParams['~USER_FIELD_CREATE_PAGE_URL'])
			? $this->arParams['~USER_FIELD_CREATE_PAGE_URL'] : '';
		$this->arResult['USER_FIELD_CREATE_SIGNATURE'] = isset($this->arParams['~USER_FIELD_CREATE_SIGNATURE'])
			? $this->arParams['~USER_FIELD_CREATE_SIGNATURE'] : '';

		$this->arResult['ENABLE_SETTINGS_FOR_ALL'] = CCrmAuthorizationHelper::CanEditOtherSettings();

		$this->arResult['ENABLE_SECTION_EDIT'] = isset($this->arParams['~ENABLE_SECTION_EDIT'])
			&& $this->arParams['~ENABLE_SECTION_EDIT'];

		$this->arResult['ENABLE_SECTION_CREATION'] = isset($this->arParams['~ENABLE_SECTION_CREATION'])
			&& $this->arParams['~ENABLE_SECTION_CREATION'];

		$this->arResult['SERVICE_URL'] = isset($this->arParams['~SERVICE_URL'])
			? $this->arParams['~SERVICE_URL'] : '';

		$this->arResult['EXTERNAL_CONTEXT_ID'] = isset($this->arParams['~EXTERNAL_CONTEXT_ID']) ? $this->arParams['~EXTERNAL_CONTEXT_ID'] : '';
		$this->arResult['CONTEXT_ID'] = isset($this->arParams['~CONTEXT_ID']) ? $this->arParams['~CONTEXT_ID'] : '';

		$this->arResult['CONTEXT'] = isset($this->arParams['~CONTEXT']) && is_array($this->arParams['~CONTEXT'])
			? $this->arParams['~CONTEXT'] : array();

		$this->arResult['DUPLICATE_CONTROL'] = isset($this->arParams['~DUPLICATE_CONTROL']) && is_array($this->arParams['~DUPLICATE_CONTROL'])
			? $this->arParams['~DUPLICATE_CONTROL'] : array();

		$this->arResult['PATH_TO_CONTACT_CREATE'] = CComponentEngine::makePathFromTemplate(
			\CrmCheckPath(
				'PATH_TO_CONTACT_DETAILS',
				isset($this->arParams['~PATH_TO_CONTACT_DETAILS']) ? $this->arParams['~PATH_TO_CONTACT_DETAILS'] : '',
				$APPLICATION->GetCurPage().'?contact_id=#contact_id#&details'
			),
			array('contact_id' => 0)
		);
		$this->arResult['PATH_TO_CONTACT_REQUISITE_SELECT'] = \CrmCheckPath(
			'PATH_TO_CONTACT_REQUISITE_SELECT',
			isset($this->arParams['~PATH_TO_CONTACT_REQUISITE_SELECT']) ? $this->arParams['~PATH_TO_CONTACT_REQUISITE_SELECT'] : '',
			$APPLICATION->GetCurPage().'?contact_id=#contact_id#&requisiteselect'
		);
		$this->arResult['PATH_TO_COMPANY_CREATE'] = CComponentEngine::makePathFromTemplate(
			\CrmCheckPath(
				'PATH_TO_COMPANY_DETAILS',
				isset($this->arParams['~PATH_TO_COMPANY_DETAILS']) ? $this->arParams['~PATH_TO_COMPANY_DETAILS'] : '',
				$APPLICATION->GetCurPage().'?company_id=#company_id#&details'
			),
			array('company_id' => 0)
		);
		$this->arResult['PATH_TO_COMPANY_REQUISITE_SELECT'] = \CrmCheckPath(
			'PATH_TO_COMPANY_REQUISITE_SELECT',
			isset($this->arParams['~PATH_TO_COMPANY_REQUISITE_SELECT']) ? $this->arParams['~PATH_TO_COMPANY_REQUISITE_SELECT'] : '',
			$APPLICATION->GetCurPage().'?company_id=#company_id#&requisiteselect'
		);

		$this->arResult['PATH_TO_REQUISITE_EDIT'] = '/thurly/components/thurly/crm.requisite.edit/slider.ajax.php?requisite_id=#requisite_id#&'.thurly_sessid_get();

		//region Languages
		$this->arResult['LANGUAGES'] = array();
		$dbResultLangs = \CLanguage::GetList($by = '', $order = '');
		while($lang = $dbResultLangs->Fetch())
		{
			$this->arResult['LANGUAGES'][] = array('LID' => $lang['LID'], 'NAME' => $lang['NAME']);
		}
		//endregion

		//Bizproc
		$this->arResult['BIZPROC_MANAGER_CONFIG'] = array();
		$bizprocEventType = $this->entityID === 0 ? CCrmBizProcEventType::Create : CCrmBizProcEventType::Edit;
		if (CCrmBizProcHelper::HasParameterizedAutoWorkflows($this->entityTypeID, $bizprocEventType))
		{
			$this->arResult['BIZPROC_MANAGER_CONFIG'] = array(
				"hasParameters" => true,
				"moduleId" => 'crm',
				"entity" => CCrmBizProcHelper::ResolveDocumentName($this->entityTypeID),
				"documentType" => CCrmOwnerType::ResolveName($this->entityTypeID),
				"autoExecuteType" => $bizprocEventType,
				'fieldName' => 'bizproc_parameters'
			);
		}
		//end Bizproc

		//Rest placement and userfield types
		$this->arResult['REST_USE'] = false;
		$this->arResult['REST_PLACEMENT_TAB_CONFIG'] = array();
		$this->arResult['USERFIELD_TYPE_ADDITIONAL'] = array();
		if(Main\ModuleManager::isModuleInstalled('rest'))
		{
			$this->arResult['REST_USE'] = true;

			$this->arResult['USERFIELD_TYPE_ADDITIONAL'] = $this->getAdditionalUserFieldTypeList();
			$this->arResult['REST_PLACEMENT_TAB_CONFIG'] = array(
				'entity' => \CCrmOwnerType::ResolveName($this->entityTypeID),
				'placement' => \Thurly\Rest\Api\UserFieldType::PLACEMENT_UF_TYPE,
			);
		}
		//end Rest placement and userfield types
	}

	protected function getAdditionalUserFieldTypeList()
	{
		$typeList = array();
		if(Main\Loader::includeModule('rest'))
		{
			$handlerList = \Thurly\Rest\PlacementTable::getHandlersList(\Thurly\Rest\Api\UserFieldType::PLACEMENT_UF_TYPE);
			foreach($handlerList as $handlerInfo)
			{
				$typeList[] = array(
					'USER_TYPE_ID' => \Thurly\Rest\UserField\Callback::getUserTypeId($handlerInfo),
					'TITLE' => $handlerInfo['TITLE'],
					'LEGEND' => $handlerInfo['COMMENT']
				);
			}
		}

		return $typeList;
	}
}