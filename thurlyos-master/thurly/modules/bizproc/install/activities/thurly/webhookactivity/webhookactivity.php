<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main\Loader;
use \Thurly\Rest\Sqs;

class CBPWebHookActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Handler" => ""
		);
	}

	public function Execute()
	{
		if(!static::checkRegister())
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$handler = $this->Handler;

		if ($handler)
		{
			$handlerData = parse_url($handler);

			if (is_array($handlerData)
				&& strlen($handlerData['host']) > 0
				&& strpos($handlerData['host'], '.') > 0
				&& ($handlerData['scheme'] == 'http' || $handlerData['scheme'] == 'https')
			)
			{
				$handler = CHTTP::urnEncode($handler);

				$queryItems = array(
					Sqs::queryItem(
						null,
						$handler,
						array(
							'document_id' => $this->GetDocumentId(),
						),
						array(),
						array(
							"sendAuth" => false,
							"sendRefreshToken" => false,
							"category" => Sqs::CATEGORY_BIZPROC,
						)
					),
				);

				\Thurly\Rest\OAuthService::getEngine()->getClient()->sendEvent($queryItems);
			}
		}

		return CBPActivityExecutionStatus::Closed;
	}

	protected static function checkRegister()
	{
		if(!Loader::includeModule('rest'))
		{
			return false;
		}

		if(!\Thurly\Rest\OAuthService::getEngine()->isRegistered())
		{
			try
			{
				\Thurly\Rest\OAuthService::register();
			}
			catch(\Thurly\Main\SystemException $e)
			{
				return false;
			}
		}

		if(!\Thurly\Rest\OAuthService::getEngine()->isRegistered())
		{
			return false;
		}

		return true;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (strlen($arTestProperties["Handler"]) <= 0)
		{
			$arErrors[] = array(
				"code" => "emptyHandler",
				"message" => GetMessage("BPWHA_EMPTY_TEXT"),
			);
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $siteId = '')
	{
		$dialog = new \Thurly\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		));

		$dialog->setMap(array(
			'Handler' => array(
				'Name' => GetMessage('BPWHA_HANDLER_NAME'),
				'FieldName' => 'handler',
				'Type' => 'text',
				'Required' => true
			)
		));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$arProperties = array(
			"Handler" => $arCurrentValues["handler"],
		);

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}