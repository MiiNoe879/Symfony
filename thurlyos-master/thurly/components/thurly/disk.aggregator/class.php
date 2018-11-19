<?php
use Thurly\Disk\Internals\BaseComponent;
use Thurly\Disk\Security\FakeSecurityContext;
use Thurly\Disk\User;
use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Disk\Storage;
use Thurly\Disk\Security\DiskSecurityContext;
use Thurly\Disk\ProxyType;
use Thurly\Main\SystemException;
use Thurly\Disk\Driver;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if(!Loader::includeModule('disk'))
{
	return;
}

class CDiskAggregatorComponent extends BaseComponent
{
	protected function prepareParams()
	{
		$this->arParams["CACHE_TIME"] = intval($this->arParams["CACHE_TIME"]);
		if ($this->arParams["SEF_MODE"] == "Y")
		{
			$this->arParams["SEF_FOLDER"] = rtrim($this->arParams["SEF_FOLDER"], "/") . "/";
		}
		else
		{
			throw new SystemException("Only SEF!");
		}

		return $this;
	}

	protected function processBeforeAction($actionName)
	{
		parent::processBeforeAction($actionName);

		$user = $this->getUser();
		if(!$user || !$user->isAuthorized() || !$user->getId())
		{
			$this->showAccessDenied();
			return false;
		}

		return true;
	}

	protected function processActionDefault()
	{
		$diskSecurityContext = $this->getSecurityContextByUser($this->getUser());
		$this->arResult["COMMON_DISK"] = array();

		$filterReadableList = array('=STORAGE.ENTITY_TYPE' => ProxyType\Common::className());
		foreach(Storage::getReadableList($diskSecurityContext, array('filter' => $filterReadableList)) as $storage)
		{
			$proxyType = $storage->getProxyType();
			if($storage->getSiteId() != SITE_ID)
			{
				continue;
			}
			$this->arResult["COMMON_DISK"][$storage->getEntityId()] = array(
				"TITLE" => $proxyType->getEntityTitle(),
				"URL" => $proxyType->getBaseUrlFolderList(),
				"ICON" => $proxyType->getEntityImageSrc(64,64),
			);
		}

		$userId = $this->getUser()->getId();
		$storage = Driver::getInstance()->getStorageByUserId($userId);
		if($storage)
		{
			$proxyType = $storage->getProxyType();
			$this->arResult["COMMON_DISK"][$storage->getEntityId()] = array(
				"TITLE" => $proxyType->getTitleForCurrentUser(),
				"URL" => $proxyType->getBaseUrlFolderList(),
				"ICON" => $proxyType->getEntityImageSrc(64,64),
			);
		}

		$this->arResult["COMMON_DISK"]["GROUP"] = array(
			"TITLE" => Loc::getMessage('DISK_AGGREGATOR_GROUP_TITLE'),
			"ID" => "bx-disk-aggregator-group-link",
		);

		$this->arResult["COMMON_DISK"]["USER"] = array(
			"TITLE" => Loc::getMessage('DISK_AGGREGATOR_USER_TITLE'),
			"ID" => "bx-disk-aggregator-user-link",
		);

		$this->arResult["COMMON_DISK"]["EXTRANET_USER"] = array(
			"TITLE" => Loc::getMessage('DISK_AGGREGATOR_EXTRANET_USER_TITLE'),
			"ID" => "bx-disk-aggregator-extranet-user-link",
		);

		$this->arResult["NETWORK_DRIVE_LINK"] = Driver::getInstance()->getUrlManager()->getHostUrl() . $this->application->GetCurPage();

		$this->includeComponentTemplate();
	}

	protected function getSecurityContextByUser($user)
	{
		$diskSecurityContext = new DiskSecurityContext($user);
		if(Loader::includeModule('socialnetwork'))
		{
			if(\CSocnetUser::isCurrentUserModuleAdmin())
			{
				$diskSecurityContext = new FakeSecurityContext($user);
			}
		}
		if(User::isCurrentUserAdmin())
		{
			$diskSecurityContext = new FakeSecurityContext($user);
		}
		return $diskSecurityContext;
	}
}