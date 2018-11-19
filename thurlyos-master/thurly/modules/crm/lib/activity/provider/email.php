<?php
namespace Thurly\Crm\Activity\Provider;

use Thurly\Main\Localization\Loc;
use Thurly\Crm\Activity;
use Thurly\Crm\Activity\CommunicationStatistics;

Loc::loadMessages(__FILE__);

class Email extends Activity\Provider\Base
{
	public static function getId()
	{
		return 'CRM_EMAIL';
	}

	public static function getTypeId(array $activity)
	{
		return 'EMAIL';
	}

	public static function getTypes()
	{
		return array(
			array(
				'NAME' => 'E-mail',
				'PROVIDER_ID' => static::getId(),
				'PROVIDER_TYPE_ID' => 'EMAIL'
			)
		);
	}

	public static function getName()
	{
		return 'E-mail';
	}

	public static function getTypeName($providerTypeId = null, $direction = \CCrmActivityDirection::Undefined)
	{
		return Loc::getMessage('CRM_ACTIVITY_PROVIDER_EMAIL_NAME');
	}

	public static function getCommunicationType($providerTypeId = null)
	{
		return static::COMMUNICATION_TYPE_EMAIL;
	}

	/**
	 * @param null|string $providerTypeId Provider type id.
	 * @return bool
	 */
	public static function canUseLiveFeedEvents($providerTypeId = null)
	{
		return true;
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

	public static function getSupportedCommunicationStatistics()
	{
		return array(
			CommunicationStatistics::STATISTICS_QUANTITY
		);
	}

	public static function checkFields($action, &$fields, $id, $params = null)
	{
		$result = new \Thurly\Main\Result();

		if (isset($fields['END_TIME']) && $fields['END_TIME'] != '')
			$fields['DEADLINE'] = $fields['END_TIME'];

		return $result;
	}

	public static function onAfterAdd($activityFields)
	{
		//region Mark incoming email as completed when reply message was sent.
		$direction = isset($activityFields['DIRECTION']) ? (int)$activityFields['DIRECTION'] : \CCrmActivityDirection::Undefined;
		$parentID = isset($activityFields['PARENT_ID']) ? (int)$activityFields['PARENT_ID'] : 0;
		if(!($direction === \CCrmActivityDirection::Outgoing && $parentID > 0))
		{
			return;
		}

		$dbResult = \CCrmActivity::GetList(
			array(),
				array('ID'=> $parentID, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID', 'DIRECTION', 'COMPLETED')
		);
		$parentFields = $dbResult->Fetch();
		if(!is_array($parentFields))
		{
			return;
		}

		$parentCompleted = isset($parentFields['COMPLETED']) && $parentFields['COMPLETED'] === 'Y';
		$parentDirection = isset($parentFields['DIRECTION']) ? (int)$parentFields['DIRECTION'] : \CCrmActivityDirection::Undefined;
		if(!$parentCompleted && $parentDirection === \CCrmActivityDirection::Incoming)
		{
			\CCrmActivity::Complete($parentID, true);
		}
		//endregion
	}

	public static function renderView(array $activity)
	{
		global $APPLICATION;

		ob_start();

		$APPLICATION->IncludeComponent(
			'thurly:crm.activity.email', '',
			array(
				'ACTIVITY' => $activity,
				'ACTION'   => 'view',
			)
		);

		return ob_get_clean();
	}

	public static function renderEdit(array $activity)
	{
		global $APPLICATION;

		ob_start();

		$APPLICATION->IncludeComponent(
			'thurly:crm.activity.email', '',
			array(
				'ACTIVITY' => $activity,
				'ACTION'   => 'create',
			)
		);

		return ob_get_clean();
	}

	public static function prepareEmailInfo(array $fields)
	{
		$direction = isset($fields['DIRECTION']) ? (int)$fields['DIRECTION'] : \CCrmActivityDirection::Undefined;
		if($direction !== \CCrmActivityDirection::Outgoing)
		{
			return null;
		}

		$settings = isset($fields['SETTINGS'])
			? (is_array($fields['SETTINGS']) ? $fields['SETTINGS'] : unserialize($fields['SETTINGS']))
			: array();
		if(!(isset($settings['IS_BATCH_EMAIL']) && $settings['IS_BATCH_EMAIL'] === false))
		{
			return null;
		}

		$result = array();
		if(isset($settings['READ_CONFIRMED']) && $settings['READ_CONFIRMED'] > 0)
		{
			$result['STATUS_TEXT'] = Loc::getMessage('CRM_ACTIVITY_PROVIDER_EMAIL_STATUS_READ');
		}
		else
		{
			$result['STATUS_TEXT'] = Loc::getMessage('CRM_ACTIVITY_PROVIDER_EMAIL_STATUS_SENT');
		}
		return $result;
	}
}
