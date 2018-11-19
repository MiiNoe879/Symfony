<?php
use Thurly\Main\Loader;
use Thurly\Main\Context;
use Thurly\Crm\WebForm\Form;
use Thurly\Main\Localization\Loc;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if (!Loader::includeModule('crm'))
{
	return;
}

Loc::loadMessages(__FILE__);

class CrmWebFormEditAjaxController extends \Thurly\Crm\WebForm\ComponentController
{
	protected function getActions()
	{
		return array(
			'get_create_fields',
			'get_destination_data',
			'copy',
			'showAdsSend',
		);
	}

	protected function get_create_fields()
	{
		if(!in_array($this->requestData['SCHEME_ID'], \Thurly\Crm\WebForm\Entity::getSchemesCodes()))
		{
			return;
		}

		$fieldSynchronizer = new \Thurly\Crm\WebForm\FieldSynchronizer();
		$syncFieldCodes = $fieldSynchronizer->getSynchronizeFields(
			$this->requestData['SCHEME_ID'],
			$this->requestData['FIELD_CODE_LIST']
		);

		$this->responseData['syncFieldCodes'] = $syncFieldCodes;
	}

	protected function copy()
	{
		global $USER;
		$copiedId = Form::copy($this->requestData['FORM_ID'], $USER->GetID());
		if(!$copiedId)
		{
			$this->errors[] = '';
		}
		else
		{
			$this->responseData['copiedId'] = $copiedId;
		}
	}

	protected function showAdsSend()
	{
		$adsType = $this->request->get('adsType');
		$providers = \Thurly\Crm\Ads\AdsForm::getProviders(array($adsType));

		ob_start();
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			'thurly:crm.ads.leadads',
			'',
			array(
				//'FORM_ID' => $this->requestData['FORM_ID'],
				'CONTAINER_NODE_ID' => $this->request->get('containerNodeId'),
				'ACCOUNT_ID' => null,
				'FORM_ID' => null,
				'CRM_FORM_ID' => $this->request->get('formId'),
				'PROVIDER' => $providers[$adsType],
			),
			null,
			array('HIDE_ICONS' => true, 'ACTIVE_COMPONENT' => 'Y')
		);
		$this->responseData['html'] = ob_get_clean();
	}

	protected function checkPermissions()
	{
		/**@var $USER \CUSER*/
		global $USER;
		$CrmPerms = new CCrmPerms($USER->GetID());
		return !$CrmPerms->HavePerm('WEBFORM', BX_CRM_PERM_NONE, 'WRITE');
	}

	protected function prepareRequestData()
	{
		$fieldCodes = $this->request->get('fieldCodes');
		if(!is_array($fieldCodes))
		{
			$fieldCodes = array();
		}

		$this->requestData = array(
			'SCHEME_ID' => intval($this->request->get('schemeId')),
			'FIELD_CODE_LIST' => $fieldCodes,
			'FORM_ID' => intval($this->request->get('form_id'))
		);
	}

	protected function get_destination_data()
	{
		$result = array('LAST' => array());

		if (!\Thurly\Main\Loader::includeModule('socialnetwork'))
			return;

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

		$this->responseData['DATA'] = $result;

	}
}

$controller = new CrmWebFormEditAjaxController();
$controller->exec();