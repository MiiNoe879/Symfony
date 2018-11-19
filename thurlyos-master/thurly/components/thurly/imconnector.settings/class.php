<?php
use \Thurly\Main\Loader,
	\Thurly\Main\Web\Uri,
	\Thurly\Main\Context,
	\Thurly\Main\LoaderException,
	\Thurly\Main\Localization\Loc;
use \Thurly\ImConnector\Output,
	\Thurly\ImConnector\Connector,
	\Thurly\ImConnector\Component;

class ImConnectorSettings extends CThurlyComponent
{
	private $error = array();
	private $messages = array();

	/**
	 * Check the connection of the necessary modules.
	 * @return bool
	 * @throws LoaderException
	 */
	protected function checkModules()
	{
		if (Loader::includeModule('imconnector'))
		{
			return true;
		}
		else
		{
			ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_MODULE_NOT_INSTALLED'));
			return false;
		}
	}

	public function saveForm()
	{
		if (!empty($this->request['settings_reload']) && !empty($this->request['settings_form']))
		{
			//If the session actual
			if(check_thurly_sessid())
			{
				$update = Output::saveDomainSite(Connector::getDomainDefault());

				if($update-> isSuccess())
					$this->messages[] = Loc::getMessage("IMCONNECTOR_COMPONENT_SETTINGS_OK_UPDATE");
				else
					$this->error[] = Loc::getMessage("IMCONNECTOR_COMPONENT_SETTINGS_NO_UPDATE");
			}
			else
			{
				$this->error[] = Loc::getMessage("IMCONNECTOR_COMPONENT_SETTINGS_SESSION_HAS_EXPIRED");
			}
		}
	}

	public function constructionForm()
	{
		if($this->request["settings_reload"] && $this->request["settings_form"])
			$this->arResult['UPDATE'] = true;

		$listActiveConnector = Connector::getListActiveConnectorReal();
		$listComponent = Connector::getListComponentConnector();

		foreach ($listActiveConnector as $id => $value)
		{
			if(isset($listComponent[$id]))
			{
				$this->arResult['CONNECTOR'][$id] = array(
					"component" => $listComponent[$id],
					"name" => $value
				);
			}
		}
	}
	
	public function executeComponent()
	{
		global $APPLICATION;

		$this->includeComponentLang('class.php');

		if($this->checkModules())
		{
			CUtil::InitJSCore( array('ajax' , 'popup' ));

			$uri = new Uri(Context::getCurrent()->getServer()->getRequestUri());
			
			if($this->request['reload'] == 'y' || $this->request['reload'] == 'Y')
			{
				$this->arResult['RELOAD'] = $this->request['ajaxid'];
				$uri->deleteParams(array('reload', 'ajaxid'));
				$uri->addParams(array('bxajaxid' => $this->arResult['RELOAD']));
				$this->arResult['URL_RELOAD'] = $uri->getUri();
			}
			else
			{
				$uri->addParams(array('settings_reload' => true, 'settings_form' => true, 'sessid' => thurly_sessid()));
				$this->arResult['SETTINGS_RELOAD'] =  $uri->getUri();
				$this->arResult['LANG_JS_SETTING'] =  Component::getJsLangMessageSetting();
				$this->constructionForm();
				$this->saveForm();
			}

			if(!empty($this->error))
				$this->arResult['error'] = $this->error;

			if(!empty($this->messages))
				$this->arResult['messages'] = $this->messages;

			if(!empty($this->arResult['RELOAD']) || !empty($this->arResult['UPDATE']))
				$APPLICATION->RestartBuffer();

			$this->includeComponentTemplate();

			if(!empty($this->arResult['RELOAD']) || !empty($this->arResult['UPDATE']))
			{
				CMain::FinalActions();
				die();
			}
		}
	}
};