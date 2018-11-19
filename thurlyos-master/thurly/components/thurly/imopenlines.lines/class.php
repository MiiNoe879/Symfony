<?
use Thurly\Main\Localization\Loc;
use Thurly\Main\Loader;
use Thurly\Main\Type\DateTime;
use Thurly\Main\Type\Date;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

Loc::loadMessages(__FILE__);

class CImOpenLinesListComponent extends \CThurlyComponent
{
	protected $errors = array();
	/** @var \Thurly\ImOpenlines\Security\Permissions */
	protected $userPermissions;

	private function showList()
	{
		global $USER;

		$allowedUserIds = \Thurly\ImOpenlines\Security\Helper::getAllowedUserIds(
			\Thurly\ImOpenlines\Security\Helper::getCurrentUserId(),
			$this->userPermissions->getPermission(\Thurly\ImOpenlines\Security\Permissions::ENTITY_LINES, \Thurly\ImOpenlines\Security\Permissions::ACTION_VIEW)
		);
		
		$limit = null;
		if (is_array($allowedUserIds))
		{
			$limit = array();
			$orm = \Thurly\ImOpenlines\Model\QueueTable::getList(Array(
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
				'*',
				'STATS_SESSION' => 'STATISTIC.SESSION',
				'STATS_MESSAGE' => 'STATISTIC.MESSAGE',
				'STATS_CLOSED' => 'STATISTIC.CLOSED',
				'STATS_IN_WORK' => 'STATISTIC.IN_WORK',
				'STATS_LEAD' => 'STATISTIC.LEAD',
			),
			'filter' => Array('=TEMPORARY' => 'N')
		),
			Array(
				'QUEUE' => 'Y'
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
			
			$dateCreate = $config['DATE_CREATE'];
			$config['DATE_CREATE_DISPLAY'] = $dateCreate ? $dateCreate->format(Date::getFormat()) : '';

			$activeChangeDate = $config['DATE_MODIFY'];
			/** @var DateTime $activeChangeDate */
			if($activeChangeDate)
			{
				$config['CHANGE_DATE_DISPLAY'] = $activeChangeDate->toUserTime()->format(IsAmPmMode() ? 'g:i a': 'H:i');
				$config['CHANGE_DATE_DISPLAY'] .= ', '. $activeChangeDate->format(Date::getFormat());
			}
			else
			{
				$config['DATE_CREATE_DISPLAY'] = '';
			}

			$config['CHANGE_BY_DISPLAY'] = $this->getUserInfo($config['MODIFY_USER_ID']);
			$config['CHANGE_BY_NOW_DISPLAY'] = $this->getUserInfo($USER->GetID());
			$config['ACTIVE_CONNECTORS'] = \Thurly\ImConnector\Connector::getListConnectedConnectorReal($config['ID']);

			$config['CAN_EDIT_CONNECTOR'] = $configManager->canEditConnector($config['ID']);

			$result[$id] = $config;
		}

		$this->arResult['PERM_CAN_EDIT'] = true;
		$this->arResult['LINES'] = $result;
		$this->arResult['PUBLIC_PATH'] = \Thurly\ImOpenLines\Common::getPublicFolder();
		$this->arResult['PATH_TO_EDIT'] = \Thurly\ImOpenLines\Common::getPublicFolder() . "list/edit.php?ID=#ID#";
		$this->arResult['PATH_TO_LIST'] = \Thurly\ImOpenLines\Common::getPublicFolder() . "list/";
		$this->arResult['PATH_TO_STATISTICS'] = \Thurly\ImOpenLines\Common::getPublicFolder() . "statistics.php?CONFIG_ID=#ID#";


		$this->includeComponentTemplate();

		return true;
	}

	public function getUserInfo($userId)
	{
		static $users = array();

		if(!$userId)
		{
			return null;
		}

		if(!$users[$userId])
		{
			// prepare link to profile
			$replaceList = array('user_id' => $userId);

			if (!isset($this->arParams['PATH_TO_USER_PROFILE']))
			{
				$extranetSiteID = 'ex';
				if (\Thurly\Main\Loader::includeModule("extranet"))
				{
					$extranetSiteID = \CExtranet::GetExtranetSiteID();
				}

				$this->arParams['PATH_TO_USER_PROFILE'] = \COption::GetOptionString(
					"socialnetwork",
					"user_page",
					SITE_DIR.'company/personal/',
					(\Thurly\Main\Loader::includeModule('extranet') && !\CExtranet::IsIntranetUser() ? $extranetSiteID : SITE_ID)
				)."user/#user_id#/";
			}
			$link = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_USER_PROFILE'], $replaceList);
			
			$userFields = \Thurly\Main\UserTable::getRowById($userId);
			if(!$userFields)
			{
				return null;
			}

			// format name
			$userName = CUser::FormatName(
				CSite::GetNameFormat(false),
				array(
					'LOGIN' => $userFields['LOGIN'],
					'NAME' => $userFields['NAME'],
					'LAST_NAME' => $userFields['LAST_NAME'],
					'SECOND_NAME' => $userFields['SECOND_NAME']
				),
				true, false
			);

			// prepare icon
			$fileTmp = CFile::ResizeImageGet(
				$userFields['PERSONAL_PHOTO'],
				array('width' => 42, 'height' => 42),
				BX_RESIZE_IMAGE_EXACT,
				false
			);
			//$userIcon = CFile::ShowImage($fileTmp['src'], 50, 50, 'border=0');
			$userIcon = $fileTmp['src'];

			$users[$userId] = array(
				'ID' => $userId,
				'NAME' => $userName,
				'LINK' => $link,
				'ICON' => $userIcon
			);
		}

		return $users[$userId];
	}
	
	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			$this->showErrors();
			return false;
		}
		
		$this->userPermissions = \Thurly\ImOpenlines\Security\Permissions::createWithCurrentUser();
		
		if (!$this->checkAccess())
		{
			$this->showErrors();
			return false;
		}

		$this->showList();
		
		return true;
	}

	protected function checkModules()
	{
		if(!Loader::includeModule('imopenlines'))
		{
			$this->errors[] = Loc::getMessage('OL_COMPONENT_MODULE_NOT_INSTALLED');
			return false;
		}
		if(!Loader::includeModule('imconnector'))
		{
			$this->errors[] = Loc::getMessage('OL_COMPONENT_MODULE_NOT_INSTALLED');
			return false;
		}

		return true;
	}
	
	protected function checkAccess()
	{
		if(!$this->userPermissions->canPerform(\Thurly\ImOpenlines\Security\Permissions::ENTITY_LINES, \Thurly\ImOpenlines\Security\Permissions::ACTION_VIEW))
		{
			$this->errors[] = Loc::getMessage('OL_COMPONENT_PERMISSION_DENIED');
			return false;
		}
		
		return true;
	}

	protected function hasErrors()
	{
		return (count($this->errors) > 0);
	}

	protected function showErrors()
	{
		if(count($this->errors) <= 0)
		{
			return;
		}

		foreach($this->errors as $error)
		{
			ShowError($error);
		}
	}
}