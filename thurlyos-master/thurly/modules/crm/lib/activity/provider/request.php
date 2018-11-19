<?php
namespace Thurly\Crm\Activity\Provider;

use Thurly\Main;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Request extends Base
{
	public static function getId()
	{
		return 'CRM_REQUEST';
	}

	public static function getTypeId(array $activity)
	{
		return 'REQUEST';
	}

	public static function getTypes()
	{
		return array(
			array(
				'NAME' => Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_NAME_NEW'),
				'PROVIDER_ID' => static::getId(),
				'PROVIDER_TYPE_ID' => 'REQUEST'
			)
		);
	}

	public static function getName()
	{
		return Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_NAME_NEW');
	}

	/**
	 * @param null|string $providerTypeId Provider type id.
	 * @param int $direction Activity direction.
	 * @return bool
	 */
	public static function isTypeEditable($providerTypeId = null, $direction = \CCrmActivityDirection::Undefined)
	{
		return false;
	}

	public static function renderView(array $activity)
	{
		$html = '<div class="crm-task-list-meet">';

		if (!empty($activity['SUBJECT']))
		{
			$html .= '<div class="crm-task-list-meet-inner">
					<div class="crm-task-list-meet-item">'.Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_PLANNER_SUBJECT_LABEL').':</div>
					<div class="crm-task-list-meet-topic">'.htmlspecialcharsbx($activity['SUBJECT']).'</div>
				</div>';
		}
		if (!empty($activity['DESCRIPTION']))
		{
			$html .= '<div class="crm-task-list-meet-inner">
					<div class="crm-task-list-meet-item">'.Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_PLANNER_DESCRIPTION_LABEL').':</div>
					<div class="crm-task-list-meet-element">'.$activity['DESCRIPTION_HTML'].'</div>
				</div>';
		}
		$html .= '</div>';

		return $html;
	}

	public static function onAfterAdd($activityFields)
	{
		static::notify($activityFields);
	}

	public static function notify($activityFields)
	{
		if(!Main\Loader::includeModule('im'))
			return;

		$notification = array(
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"TO_USER_ID" => (int)$activityFields['RESPONSIBLE_ID'],
			"FROM_USER_ID" => (int)$activityFields['AUTHOR_ID'],
			"NOTIFY_TYPE" => IM_NOTIFY_FROM,
			"NOTIFY_MODULE" => "crm",
			"NOTIFY_EVENT" => "requestCreated",
			"NOTIFY_TAG" => "CRM|CRM_REQUEST|".$activityFields['ID'],
			"NOTIFY_MESSAGE" => Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_NOTIFY', array(
				'#title#' =>  '<a href="'.\CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::Activity, $activityFields['ID']).'">'.$activityFields['SUBJECT'].'</a>'
			)),
			"NOTIFY_MESSAGE_OUT" => Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_NOTIFY', array(
				'#title#' => $activityFields['SUBJECT']
			)),
		);

		if ($notification['TO_USER_ID'] === $notification['FROM_USER_ID'])
		{
			//send from system
			$notification['NOTIFY_TYPE'] = IM_NOTIFY_SYSTEM;
			unset($notification['FROM_USER_ID']);
		}

		\CIMNotify::Add($notification);
	}

	/**
	 * @return array
	 */
	public static function getTypesFilterPresets()
	{
		return array(
			array(
				'NAME' => Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_NAME_NEW'),
				'PROVIDER_TYPE_ID' => 'REQUEST'
			),
		);
	}
}
