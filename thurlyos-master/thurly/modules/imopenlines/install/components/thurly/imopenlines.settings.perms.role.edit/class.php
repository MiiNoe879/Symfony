<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Thurly\Main\Localization\Loc;
use Thurly\ImOpenlines\Model;

Loc::loadMessages(__FILE__);

class CImOpenlinesRoleEditComponent extends CThurlyComponent
{
	protected $new = false;
	protected $saveMode = false;
	protected $id;
	protected $errors;

	public function __construct($component)
	{
		parent::__construct($component);

		$this->errors = new \Thurly\Main\ErrorCollection();

		$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();
		$this->id = (int)$request['ID'];
		if($this->id == 0)
			$this->new = true;

		if($request['act'] === 'save' && check_thurly_sessid())
			$this->saveMode = true;
	}

	protected function prepareData()
	{
		if($this->errors->isEmpty())
		{
			$role = Model\RoleTable::getById($this->id)->fetch();
			if(!$role)
			{
				$this->errors[] = new \Thurly\Main\Error(Loc::getMessage('IMOL_ROLE_NOT_FOUND'));
				$this->new = true;
			}
			else
			{
				$this->arResult['NAME'] = $role['NAME'];
			}
			$rolePermissions = \Thurly\ImOpenlines\Security\Permissions::getNormalizedPermissions(
				\Thurly\ImOpenlines\Security\RoleManager::getRolePermissions($this->id)
			);
			$this->arResult['ID'] = ($this->new ? 0 : $this->id);
			$this->arResult['PERMISSIONS'] = $rolePermissions;
		}
		else
		{
			$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();
			$this->arResult['ID'] = ($this->new ? 0 : $this->id);
			$this->arResult['NAME'] = (string)$request['NAME'];
			$this->arResult['PERMISSIONS'] = (array)$request['PERMISSIONS'];
			$this->arResult['ERRORS'] = $this->errors;
		}

		$this->arResult['PERMISSION_MAP'] = \Thurly\ImOpenlines\Security\Permissions::getMap();
		$this->arResult['PERMISSIONS_URL'] = \Thurly\ImOpenlines\Common::getPublicFolder()."permissions.php";
		$this->arResult['CAN_EDIT'] = \Thurly\ImOpenlines\Security\Helper::canUse();
		if(!$this->arResult['CAN_EDIT'])
		{
			$this->arResult['TRIAL'] = \Thurly\ImOpenlines\Security\Helper::getTrialText();
		}

	}

	protected function save()
	{
		$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();

		$roleId = (int)$request['ID'];
		$roleName = (string)$request['NAME'];
		$permissions = $request['PERMISSIONS'];
		
		if($roleName == '')
		{
			$this->errors[] = new \Thurly\Main\Error(Loc::getMessage('IMOL_ROLE_ERROR_EMPTY_NAME'));
			return false;
		}

		if($role = Model\RoleTable::getById($roleId)->fetch())
		{
			$saveResult = Model\RoleTable::update(
				$role['ID'],
				array(
					'NAME' => $roleName
				)
			);
		}
		else
		{
			$saveResult = Model\RoleTable::add(array(
				'NAME' => $request['NAME']
			));
			$roleId = $saveResult->getId();
		}

		if(!$saveResult->isSuccess())
		{
			$this->errors[] = new \Thurly\Main\Error(Loc::getMessage('IMOL_ROLE_SAVE_ERROR'));
			return false;
		}
		else if(is_array($permissions))
		{
			\Thurly\ImOpenlines\Security\RoleManager::setRolePermissions($roleId, $permissions);
		}

		return true;
	}

	public function executeComponent()
	{
		$permissions = \Thurly\ImOpenlines\Security\Permissions::createWithCurrentUser();
		if(!$permissions->canPerform(\Thurly\ImOpenlines\Security\Permissions::ENTITY_SETTINGS, \Thurly\ImOpenlines\Security\Permissions::ACTION_MODIFY))
		{
			ShowError(Loc::getMessage('IMOL_ROLE_ERROR_INSUFFICIENT_RIGHTS'));
			return false;
		}

		if($this->saveMode)
		{
			if(\Thurly\ImOpenlines\Security\Helper::canUse())
			{
				if($this->save())
				{
					LocalRedirect(\Thurly\ImOpenlines\Common::getPublicFolder()."permissions.php");
					return false;
				}
			}
			else
			{
				$this->errors[] = new \Thurly\Main\Error(Loc::getMessage('IMOL_ROLE_LICENSE_ERROR'));
			}
		}

		$this->prepareData();
		$this->includeComponentTemplate();
		return $this->arResult;
	}
}