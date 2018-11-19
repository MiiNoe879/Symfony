<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Component parameters:
 * CALL_ID
 */

use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Voximplant\Security;

Loc::loadMessages(__FILE__);

class CVoximplantTranscriptViewComponent extends \CThurlyComponent
{
	const MODULE = 'voximplant';
	protected $callId;
	protected $call;

	public function executeComponent()
	{
		if (!Loader::includeModule(self::MODULE))
			return false;

		$this->init();

		if(!$this->call)
			return false;

		if(!$this->checkAccess())
			return false;

		$this->arResult = $this->prepareData();
		$this->includeComponentTemplate();
		return $this->arResult;
	}

	protected function init()
	{
		$this->callId = $this->arParams['CALL_ID'];
		$this->call = \Thurly\Voximplant\StatisticTable::getByCallId($this->callId);
	}

	protected function checkAccess()
	{
		$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
		$allowedUserIds =  Security\Helper::getAllowedUserIds(
			Security\Helper::getCurrentUserId(),
			$permissions->getPermission(Security\Permissions::ENTITY_CALL_RECORD, Security\Permissions::ACTION_LISTEN)
		);

		return is_null($allowedUserIds) ? true : in_array($this->call['PORTAL_USER_ID'], $allowedUserIds);
	}

	protected function prepareData()
	{
		$result = array(
			'USER' => $this->getUserInfo($this->call['PORTAL_USER_ID']),
			'CLIENT' => $this->getCrmClientInfo($this->call),
		);

		if($this->call['TRANSCRIPT_ID'] > 0)
		{
			$result['LINES'] = array();
			$cursor = \Thurly\Voximplant\Model\TranscriptLineTable::getList(array(
				'filter' => array('=TRANSCRIPT_ID' => $this->call['TRANSCRIPT_ID']),
				'order' => array('START_TIME' => 'ASC')
			));
			while ($row = $cursor->fetch())
			{
				$result['LINES'][] = array(
					'SIDE' => $row['SIDE'],
					'MESSAGE' => $row['MESSAGE'],
					'START_TIME' => self::formatSeconds($row['START_TIME'])
				);
			}
		}
		else if($this->call['TRANSCRIPT_PENDING'] == 'Y')
		{

		}
		else
		{

		}

		return $result;
	}

	protected function getUserInfo($userId)
	{
		$user = Thurly\Main\UserTable::getById($userId)->fetch();
		if (!$user)
			return false;

		$userPhoto = \CFile::ResizeImageGet(
			$user['PERSONAL_PHOTO'],
			array('width' => 37, 'height' => 37),
			BX_RESIZE_IMAGE_EXACT,
			false,
			false,
			true
		);

		return array(
			'ID' => $user['ID'],
			'NAME' => \CUser::FormatName(CSite::GetNameFormat(false), $user, true, false),
			'PHOTO' => $userPhoto ? $userPhoto['src']: '',
			'POST' => $user['WORK_POSITION'],
			'PROFILE_PATH' => CComponentEngine::makePathFromTemplate($this->getPathToUserProfile(), array(
				'user_id' => $user['ID']
			))
		);
	}

	protected function getPathToUserProfile()
	{
		$pathToUser = COption::GetOptionString("main", "TOOLTIP_PATH_TO_USER", false, SITE_ID);
		return ($pathToUser ? $pathToUser : SITE_DIR."company/personal/user/#user_id#/");
	}

	protected function getCrmClientInfo($callFields)
	{
		if($callFields['CRM_ENTITY_TYPE'] != '' && $callFields['CRM_ENTITY_ID'] > 0 && Loader::includeModule('crm'))
		{
			$name = CCrmOwnerType::GetCaption(CCrmOwnerType::ResolveID($callFields['CRM_ENTITY_TYPE']), $callFields['CRM_ENTITY_ID']);
			if($callFields['CRM_ENTITY_TYPE'] == CCrmOwnerType::ContactName)
			{
				$contact = CCrmContact::GetByID($callFields['CRM_ENTITY_ID']);
				if($contact['PHOTO'] > 0)
				{
					$photo = \CFile::ResizeImageGet(
						$contact['PHOTO'],
						array('width' => 37, 'height' => 37),
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						true
					);
				}
			}
			return array(
				'NAME' => $name,
				'PHOTO' => $photo ? $photo['src']: '',
			);
		}

		return array(
			'NAME' => Loc::getMessage("VOX_TRANSCRIPT_CLIENT"),
			'PHOTO' => '',
		);
	}

	protected static function formatSeconds($seconds)
	{
		$seconds = (int)$seconds;
		$minutes = floor($seconds / 60);
		$secondsRemainder = $seconds % 60;

		return sprintf("%02d:%02d", $minutes, $secondsRemainder);
	}
}