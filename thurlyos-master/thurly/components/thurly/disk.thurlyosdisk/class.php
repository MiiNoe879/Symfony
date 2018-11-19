<?php
use Thurly\Disk\Internals\BaseComponent;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Page\Asset;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!\Thurly\Main\Loader::includeModule('disk'))
{
	return;
}

Loc::loadMessages(__FILE__);

class CDiskThurlyOSDiskComponent extends BaseComponent
{
	protected function processActionDefault()
	{
		$pathToAjax = isset($this->arParams['AJAX_PATH'])? $this->arParams['AJAX_PATH'] : '/thurly/components/thurly/disk.thurlyosdisk/ajax.php';

		$diskQuota = new CDiskQuota();
		$quota = $diskQuota->getDiskQuota();
		$this->arResult['showDiskQuota'] = false; //$quota !== true; //now without quota
		$this->arResult['diskSpace'] = (float)COption::getOptionInt('main', 'disk_space')*1024*1024;
		$this->arResult['quota'] = $quota;
		$this->arResult['ajaxIndex'] = $pathToAjax;
		$this->arResult['ajaxStorageIndex'] = '/desktop_app/storage.php';
		$this->arResult['isInstalledDisk'] = \Thurly\Disk\Desktop::isDesktopDiskInstall();
		$this->arResult['personalLibIndex'] = '/company/personal/user/' . $this->getUser()->getId() . '/disk/path/';
		$this->arResult['pathTemplateToRestoreObject'] = \Thurly\Disk\Driver::getInstance()
			->getUrlManager()
			->getUrlFocusController(
				'showObjectInTrashCanGrid',
				array('objectId' => 'placeForObjectId',)
		);
		$this->arResult['enableShowingNotify'] =
			\Thurly\Main\Loader::includeModule('im') && 
			CIMSettings::getSetting('notify', 'site|disk|files')
		;

		$this->arResult['isInstalledPull'] = (bool)isModuleInstalled('pull');
		$this->arResult['currentUser'] = array(
			'id' => $this->getUser()->getId(),
			'formattedName' => $this->getUser()->getFormattedName(),
		);
		Asset::getInstance()->addJs('/thurly/components/thurly/disk.thurlyosdisk/disk.js');

		$this->includeComponentTemplate();
	}
}
