<?php
use \Thurly\Main\Loader,
	\Thurly\Main\Web\Uri,
	\Thurly\Main\Context,
	\Thurly\Main\LoaderException,
	\Thurly\Main\Localization\Loc;
use \Thurly\ImConnector\Connector,
	\Thurly\ImConnector\Component;
use \Thurly\ImOpenLines\Common,
	\Thurly\ImOpenLines\Config,
	\Thurly\ImOpenLines\Helper,
	\Thurly\ImOpenlines\Security,
	\Thurly\ImOpenlines\Model\QueueTable,
	\Thurly\ImOpenlines\Security\Permissions;

class ImConnectorConnectorSettings extends CThurlyComponent
{
	/** @var \Thurly\ImOpenlines\Security\Permissions */
	protected $userPermissions;
	/**
	 * Check the connection of the necessary modules.
	 * @return bool
	 * @throws LoaderException
	 */
	protected function checkModules()
	{
		if (Loader::includeModule('imopenlines') && Loader::includeModule('imconnector'))
		{
			return true;
		}
		else
		{
			if(!Loader::includeModule('imopenlines') && !Loader::includeModule('imconnector'))
			{
				ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_MODULE_IMOPENLINES_NOT_INSTALLED'));
				ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_MODULE_IMCONNECTOR_NOT_INSTALLED'));
			}
			elseif(!Loader::includeModule('imopenlines'))
			{
				ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_MODULE_IMOPENLINES_NOT_INSTALLED'));
			}
			else
			{
				ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_MODULE_IMCONNECTOR_NOT_INSTALLED'));
			}

			return false;
		}
	}

	private function showList()
	{
		$allowedUserIds = Security\Helper::getAllowedUserIds(
			Security\Helper::getCurrentUserId(),
			$this->userPermissions->getPermission(Permissions::ENTITY_CONNECTORS, Permissions::ACTION_MODIFY)
		);

		$limit = null;
		if (is_array($allowedUserIds))
		{
			$limit = array();
			$orm = QueueTable::getList(Array(
				'filter' => Array(
					'=USER_ID' => $allowedUserIds
				)
			));
			while ($row = $orm->fetch())
			{
				$limit[$row['CONFIG_ID']] = $row['CONFIG_ID'];
			}
		}

		$configManager = new \Thurly\ImOpenLines\Config();
		$result = $configManager->getList(Array(
			'select' => Array(
				'ID',
				'NAME' => 'LINE_NAME'
			),
			'filter' => Array('=TEMPORARY' => 'N')
		));
		foreach ($result as $id => $config)
		{
			if (!is_null($limit))
			{
				if (!isset($limit[$config['ID']]) && !in_array($config['MODIFY_USER_ID'], $allowedUserIds))
				{
					unset($result[$id]);
					continue;
				}
			}

			if(empty($this->arResult['LINE']) && $id === 0)
				$config['ACTIVE'] = true;
			elseif(!empty($this->arResult['LINE']) && $config['ID'] == $this->arResult['LINE'])
				$config['ACTIVE'] = true;

			$config['URL'] = str_replace(array('#ID#', '#LINE#'), array($this->arResult['ID'], $config['ID']), $this->arResult['PATH_TO_CONNECTOR_LINE']);

			if(!empty($config['ACTIVE']))
			{
				$this->arResult['ACTIVE_LINE'] = $config;
				if(!empty($this->arResult['ACTIVE_LINE']['NAME']))
				{
					$this->arResult['ACTIVE_LINE']['~NAME'] = $this->arResult['ACTIVE_LINE']['NAME'];
					$this->arResult['ACTIVE_LINE']['NAME'] = htmlspecialcharsbx($this->arResult['ACTIVE_LINE']['NAME']);
				}
				$uri = new Uri(str_replace('#ID#', $config['ID'], $this->arResult['PATH_TO_EDIT']));
				$uri->addParams(array('back_url' => urlencode(
					str_replace(array('#ID#', '#LINE#'), array($this->arResult['ID'], $config['ID']), $this->arResult['PATH_TO_CONNECTOR_LINE']))
				));
				$this->arResult['ACTIVE_LINE']['URL_EDIT'] = $uri->getUri();
			}

			$result[$id] = $config;
		}

		return $result;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		$this->includeComponentLang('class.php');
		Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/thurly/components/thurly/imconnector.settings.status/class.php');
		Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/thurly/components/thurly/imconnector.settings/class.php');

		if($this->checkModules())
		{
			$this->arResult['PUBLIC_PATH'] = Common::getPublicFolder();

			if(empty($this->arParams['connector']))
				$this->arResult['ID'] = $this->request['ID'];
			else
				$this->arResult['ID'] = $this->arParams['connector'];

			$this->arResult['ID'] = Connector::getConnectorRealId($this->arResult['ID']);

			if(!empty($this->arResult['ID']) && Connector::isConnector($this->arResult['ID']))
			{
				if($this->request['reload'] == 'y' || $this->request['reload'] == 'Y')
				{
					CUtil::InitJSCore( array('ajax' , 'popup' ));

					$uri = new Uri(Context::getCurrent()->getServer()->getRequestUri());

					$this->arResult['RELOAD'] = $this->request['ajaxid'];
					$uri->deleteParams(array('reload', 'ajaxid'));
					$uri->addParams(array('bxajaxid' => $this->arResult['RELOAD']));
					$this->arResult['URL_RELOAD'] = $uri->getUri();
				}
				else
				{
					$this->userPermissions = Permissions::createWithCurrentUser();

					if(!empty($this->request['LINE']))
						$this->arResult['LINE'] = $this->request['LINE'];
					$listComponentConnector = Connector::getListComponentConnector();
					$this->arResult['COMPONENT'] = $listComponentConnector[$this->arResult['ID']];
					$this->arResult['NAME'] = Connector::getNameConnectorReal($this->arResult['ID'], false);
					$this->arResult['NAME_SMALL'] = Connector::getNameConnectorReal($this->arResult['ID'], true);
					$this->arResult['LANG_JS_SETTING'] = Component::getJsLangMessageSetting();

					$this->arResult['PATH_TO_EDIT'] = $this->arResult['PUBLIC_PATH'] . "list/edit.php?ID=#ID#";

					if(empty($this->arParams['connector']))
					{
						$this->arResult['PATH_TO_CONNECTOR'] = $this->arResult['PUBLIC_PATH'] . "connector/?ID=#ID#";
						$this->arResult['PATH_TO_CONNECTOR_LINE'] = $this->arResult['PUBLIC_PATH'] . "connector/?ID=#ID#&LINE=#LINE#";
					}
					else
					{
						$this->arResult['PATH_TO_CONNECTOR'] = $this->arResult['PUBLIC_PATH'] . "connector/#ID#/";
						$this->arResult['PATH_TO_CONNECTOR_LINE'] = $this->arResult['PUBLIC_PATH'] . "connector/#ID#/?LINE=#LINE#";
					}

					$this->arResult['LIST_LINE'] = $this->showList();

					if(empty($this->arResult['ACTIVE_LINE']) && !empty($this->arResult['LINE']))
					{
						LocalRedirect($this->arResult['PUBLIC_PATH']);
					}

					$configManager = new Config();
					if(($configManager->canActivateLine() || empty($this->arParams['connector']))
						&& $this->userPermissions->canPerform(Permissions::ENTITY_LINES, Permissions::ACTION_MODIFY))
					{
						$this->arResult['PATH_TO_ADD_LINE'] = Helper::getAddUrl();
					}
				}

				$APPLICATION->SetTitle(Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CONNECT') . " " . $this->arResult['NAME']);

				if(!empty($this->arResult['RELOAD']))
					$APPLICATION->RestartBuffer();

				$this->includeComponentTemplate();

				if(!empty($this->arResult['RELOAD']))
				{
					CMain::FinalActions();
					die();
				}
			}
			else
			{
				LocalRedirect($this->arResult['PUBLIC_PATH']);
			}
		}
	}
};