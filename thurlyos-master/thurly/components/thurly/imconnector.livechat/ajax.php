<?php
use Thurly\Main\Loader;
use Thurly\Main\Context;
use Thurly\Main\Localization\Loc;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if (!Loader::includeModule('imconnector') || !Loader::includeModule('imopenlines'))
{
	return;
}

Loc::loadMessages(__FILE__);

class ImConnectorLiveChatAjaxController
{
	protected $errors = array();
	protected $action = null;
	protected $responseData = array();
	protected $requestData = array();

	/** @var \Thurly\Main\HttpRequest $request */
	protected $request = array();

	protected function getActions()
	{
		return array(
			'formatName',
			'checkName',
		);
	}

	protected function formatName()
	{
		$alias = \Thurly\ImOpenLines\LiveChatManager::prepareAlias($this->requestData['ALIAS']);
		$this->responseData['ALIAS'] = $alias? $alias: '';
	}

	protected function checkName()
	{
		$manager = new \Thurly\ImOpenLines\LiveChatManager($this->requestData['CONFIG_ID']);
		$result = $manager->checkAvailableName($this->requestData['ALIAS']);

		$this->responseData['AVAILABLE'] = $result? 'Y':'N';
	}

	protected function checkPermissions()
	{
		$configManager = new \Thurly\ImOpenLines\Config();
		return $configManager->canEditLine($this->requestData['CONFIG_ID']);
	}

	protected function prepareRequestData()
	{
		$this->requestData = array(
			'CONFIG_ID' => intval($this->request->get('CONFIG_ID')),
			'ALIAS' => $this->request->get('ALIAS')
		);
		\CUtil::decodeURIComponent($this->requestData);
	}

	protected function giveResponse()
	{
		global $APPLICATION;
		$APPLICATION->restartBuffer();

		header('Content-Type:application/json; charset=UTF-8');
		echo \Thurly\Main\Web\Json::encode(
			$this->responseData + array(
				'error' => $this->hasErrors(),
				'text' => implode('<br>', $this->errors),
			)
		);

		\CMain::finalActions();
		exit;
	}

	protected function getActionCall()
	{
		return array($this, $this->action);
	}

	protected function hasErrors()
	{
		return count($this->errors) > 0;
	}

	protected function check()
	{
		if(!$this->checkPermissions())
		{
			$this->errors[] = Loc::getMessage('IMCONNECTOR_PERMISSION_DENIED');
		}
		if(!in_array($this->action, $this->getActions()))
		{
			$this->errors[] = 'Action "' . $this->action . '" not found.';
		}
		elseif(!check_thurly_sessid() || !$this->request->isPost())
		{
			$this->errors[] = 'Security error.';
		}
		elseif(!is_callable($this->getActionCall()))
		{
			$this->errors[] = 'Action method "' . $this->action . '" not found.';
		}

		return !$this->hasErrors();
	}

	public function exec()
	{
		$this->request = Context::getCurrent()->getRequest();
		$this->action = $this->request->get('ACTION');

		$this->prepareRequestData();

		if($this->check())
		{
			call_user_func_array($this->getActionCall(), array($this->requestData));
		}
		$this->giveResponse();
	}
}

$controller = new ImConnectorLiveChatAjaxController();
$controller->exec();