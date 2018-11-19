<?php
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

$siteId = '';
if (isset($_REQUEST['site_id']) && is_string($_REQUEST['site_id']))
	$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site_id']), 0, 2);

if (!$siteId)
	define('SITE_ID', $siteId);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

/**
 * @global CUser $USER
 */

if(!CModule::IncludeModule('crm') || !CModule::IncludeModule('bizproc'))
	die();

global $DB, $APPLICATION;

$curUser = CCrmSecurityHelper::GetCurrentUser();
if (!$curUser || !$curUser->IsAuthorized() || !check_thurly_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	die();
}

CUtil::JSPostUnescape();

$action = !empty($_REQUEST['ajax_action']) ? $_REQUEST['ajax_action'] : null;

if (empty($action))
	die('Unknown action!');

$APPLICATION->ShowAjaxHead();
$action = strtoupper($action);

$sendResponse = function($data, array $errors = array(), $plain = false)
{
	if ($data instanceof Thurly\Main\Result)
	{
		$errors = $data->getErrorMessages();
		$data = $data->getData();
	}

	$result = array('DATA' => $data, 'ERRORS' => $errors);
	$result['SUCCESS'] = count($errors) === 0;
	if(!defined('PUBLIC_AJAX_MODE'))
	{
		define('PUBLIC_AJAX_MODE', true);
	}
	$GLOBALS['APPLICATION']->RestartBuffer();
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

	if ($plain)
	{
		$result = $result['DATA'];
	}

	echo \Thurly\Main\Web\Json::encode($result);
	CMain::FinalActions();
	die();
};
$sendError = function($error) use ($sendResponse)
{
	$sendResponse(array(), array($error));
};

$sendHtmlResponse = function($html)
{
	if(!defined('PUBLIC_AJAX_MODE'))
	{
		define('PUBLIC_AJAX_MODE', true);
	}
	header('Content-Type: text/html; charset='.LANG_CHARSET);
	echo $html;
	CMain::FinalActions();
	die();
};

$checkConfigWritePerms = function() use ($curUser, $sendError)
{
	$CrmPerms = new CCrmPerms($curUser->GetID());
	if (!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE'))
		$sendError('Access denied!');
};

switch ($action)
{
	case 'GET_ROBOT_DIALOG':
		//Check permissions.
		$checkConfigWritePerms();

		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		$robotData = isset($_REQUEST['robot']) && is_array($_REQUEST['robot']) ? $_REQUEST['robot'] : null;
		if (!$robotData)
			$sendError('Empty robot data.');

		$context = isset($_REQUEST['context']) && is_array($_REQUEST['context']) ? $_REQUEST['context'] : null;

		ob_start();
		$APPLICATION->includeComponent(
			'thurly:crm.automation',
			'',
			array(
				'ACTION' => 'ROBOT_SETTINGS',
				'ENTITY_TYPE_ID' => $entityTypeId,
				'ROBOT_DATA' => $robotData,
				'REQUEST' => $_REQUEST['form_name'],
				'CONTEXT' => $context
			)
		);
		$dialog = ob_get_clean();

		$sendHtmlResponse($dialog);
		break;

	case 'SAVE_AUTOMATION':

		//Check permissions.
		$checkConfigWritePerms();

		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		$documentType = array(
			'crm',
			CCrmBizProcHelper::ResolveDocumentName($entityTypeId),
			\CCrmOwnerType::ResolveName($entityTypeId)
		);

		//save Templates and Robots
		$templates = isset($_REQUEST['templates']) && is_array($_REQUEST['templates']) ? $_REQUEST['templates'] : array();
		$errors = array();

		//save Triggers
		$updatedTriggers = array();
		$triggers = isset($_REQUEST['triggers']) && is_array($_REQUEST['triggers']) ? $_REQUEST['triggers'] : array();
		foreach ($triggers as $trigger)
		{
			$triggerId = isset($trigger['ID']) ? (int)$trigger['ID'] : 0;

			if (isset($trigger['DELETED']) && $trigger['DELETED'] === 'Y')
			{
				if ($triggerId > 0)
					\Thurly\Crm\Automation\Trigger\Entity\TriggerTable::delete($triggerId);
				continue;
			}

			if ($triggerId > 0)
			{
				\Thurly\Crm\Automation\Trigger\Entity\TriggerTable::update($triggerId, array(
					'NAME' => $trigger['NAME'],
					'ENTITY_STATUS' => $trigger['ENTITY_STATUS'],
					'APPLY_RULES' => is_array($trigger['APPLY_RULES']) ? $trigger['APPLY_RULES'] : null
				));
			}
			elseif (isset($trigger['CODE']) && isset($trigger['ENTITY_STATUS']))
			{
				$triggerClass = \Thurly\Crm\Automation\Factory::getTriggerByCode($trigger['CODE']);
				if (!$triggerClass)
					continue;

				$addResult = \Thurly\Crm\Automation\Trigger\Entity\TriggerTable::add(array(
					'NAME' => $trigger['NAME'],
					'ENTITY_TYPE_ID' => $entityTypeId,
					'ENTITY_STATUS' => $trigger['ENTITY_STATUS'],
					'CODE' => $trigger['CODE'],
					'APPLY_RULES' => is_array($trigger['APPLY_RULES']) ? $trigger['APPLY_RULES'] : null
				));

				if ($addResult->isSuccess())
					$trigger['ID'] = $addResult->getId();
			}
			$updatedTriggers[] = $trigger;
		}

		$updatedTemplates = array();
		foreach ($templates as $templateData)
		{
			$template = new \Thurly\Crm\Automation\Engine\Template($templateData);
			$robots = isset($templateData['ROBOTS']) && is_array($templateData['ROBOTS']) ? $templateData['ROBOTS'] : array();

			$result = $template->save($robots, $curUser->GetID());
			if ($result->isSuccess())
				$updatedTemplates[] = $template->toArray();
			else
				$errors = array_merge($errors, $result->getErrorMessages());
		}

		$sendResponse(array('templates' => $updatedTemplates, 'triggers' => $updatedTriggers), $errors);

		break;

	case 'SAVE_ROBOT_SETTINGS':
		//Check permissions.
		$checkConfigWritePerms();

		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		$robotData = isset($_REQUEST['robot']) && is_array($_REQUEST['robot']) ? $_REQUEST['robot'] : null;
		if (!$robotData)
			$sendError('Empty robot data.');

		$requestData = isset($_POST['form_data']) && is_array($_POST['form_data']) ? $_POST['form_data'] : array();

		$template = new \Thurly\Crm\Automation\Engine\Template(array(
			'ENTITY_TYPE_ID' => $entityTypeId
		));
		$saveResult = $template->saveRobotSettings($robotData, $requestData);

		if ($saveResult->isSuccess())
		{
			$data = $saveResult->getData();
			CThurlyComponent::includeComponentClass('thurly:crm.automation');
			$data['robot']['viewData'] = \CrmAutomationComponent::getRobotViewData(
				$data['robot'],
				\Thurly\Crm\Automation\Engine\Template::getAvailableRobots($entityTypeId),
				array(
					'crm',
					\CCrmBizProcHelper::ResolveDocumentName($entityTypeId),
					\CCrmOwnerType::ResolveName($entityTypeId)
				)
			);

			$sendResponse(array('robot' => $data['robot']));
		}
		else
		{
			$sendError($saveResult->getErrorMessages());
		}
		break;

	case 'GET_ROBOT_AJAX_RESPONSE':
		//Check permissions.
		$checkConfigWritePerms();

		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		$robotData = isset($_REQUEST['robot']) && is_array($_REQUEST['robot']) ? $_REQUEST['robot'] : null;
		if (!$robotData)
			$sendError('Empty robot data.');

		$context = isset($_REQUEST['context']) && is_array($_REQUEST['context']) ? $_REQUEST['context'] : null;

		$template = new \Thurly\Crm\Automation\Engine\Template(array(
			'ENTITY_TYPE_ID' => $entityTypeId
		));

		$response = $template->getRobotAjaxResponse($robotData, $_REQUEST);
		$sendResponse($response);
		break;

	case 'GET_DESTINATION_DATA':
		//Check permissions.
		$checkConfigWritePerms();

		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		CThurlyComponent::includeComponentClass('thurly:crm.automation');
		$result = \CrmAutomationComponent::getDestinationData($entityTypeId);
		$sendResponse($result);
		break;

	case 'GET_LOG':
		//Check permissions.
		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		$entityId = isset($_REQUEST['entity_id']) ? (int)$_REQUEST['entity_id'] : 0;
		if ($entityId <= 0)
			$sendError('Wrong entity id.');

		if (!\CCrmAuthorizationHelper::CheckReadPermission($entityTypeId, $entityId))
			$sendError('Access denied!');

		if (!\Thurly\Crm\Automation\Factory::isAutomationAvailable($entityTypeId))
			$sendError('Access denied!');

		$tracker = new \Thurly\Crm\Automation\Tracker($entityTypeId, $entityId);

		$sendResponse(array('LOG' =>$tracker->getLog()));
		break;

	case 'GET_WEBHOOK_HANDLER':
		//Check permissions.
		$checkConfigWritePerms();

		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		CThurlyComponent::includeComponentClass('thurly:crm.automation');
		$result = \CrmAutomationComponent::getWebhookHandler($curUser->GetID(), $entityTypeId);
		$sendResponse($result);
		break;

	case 'GET_AVAILABLE_TRIGGERS':
		//Check permissions.
		$checkConfigWritePerms();

		$entityTypeId = isset($_REQUEST['entity_type_id']) ? (int)$_REQUEST['entity_type_id'] : \CCrmOwnerType::Undefined;
		if (!\CCrmOwnerType::IsDefined($entityTypeId))
			$sendError('Wrong entity type.');

		$sendResponse(\Thurly\Crm\Automation\Factory::getAvailableTriggers($entityTypeId));
		break;


	case 'GET_WEBFORM_FORMS':
		//Check permissions.
		$checkConfigWritePerms();

		$forms = array();

		$forms = \Thurly\Crm\WebForm\Internals\FormTable::getList(array(
			'select' => array('ID', 'NAME'),
			'order' => array('NAME' => 'ASC', 'ID' => 'ASC'),
		))->fetchAll();
		$sendResponse(array('forms' => $forms));
		break;

	case 'GET_OPENLINE_CONFIGS':
		//Check permissions.
		$checkConfigWritePerms();
		$configs = array();

		if (CModule::IncludeModule('imopenlines'))
		{
			$orm = \Thurly\ImOpenLines\Model\ConfigTable::getList(Array(
				'filter' => Array(
					'=TEMPORARY' => 'N'
				)
			));
			while ($config = $orm->fetch())
			{
				$configs[] = array(
					'ID' => $config['ID'],
					'NAME' => $config['LINE_NAME']
				);
			}
		}
		$sendResponse(array('configs' => $configs));
		break;

	default:
		$sendError('Unknown action!');
		break;
}