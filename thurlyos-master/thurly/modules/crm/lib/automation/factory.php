<?php
namespace Thurly\Crm\Automation;

use Thurly\ThurlyOS\Feature;
use Thurly\Crm\Automation\Target;
use Thurly\Crm\Automation\Trigger\BaseTrigger;
use Thurly\Main\Loader;
use Thurly\Main\NotSupportedException;
use Thurly\Crm\Settings\LeadSettings;

class Factory
{
	private static $supportedEntityTypes = array(
		\CCrmOwnerType::Lead,
		\CCrmOwnerType::Deal
	);

	private static $triggerRegistry;
	private static $featuresCache = array();

	public static function isAutomationAvailable($entityTypeId, $ignoreLicense = false)
	{
		if (!Helper::isBizprocEnabled() || !static::isSupported($entityTypeId))
			return false;

		if (!$ignoreLicense && Loader::includeModule('thurlyos'))
		{
			$feature = 'crm_automation_'.strtolower(\CCrmOwnerType::ResolveName($entityTypeId));

			if (!isset(static::$featuresCache[$feature]))
			{
				static::$featuresCache[$feature] = Feature::isFeatureEnabled($feature);
			}

			return static::$featuresCache[$feature];
		}

		return true;
	}

	public static function canUseBizprocDesigner()
	{
		if (Loader::includeModule('thurlyos'))
		{
			$feature = 'crm_automation_designer';
			if (!isset(static::$featuresCache[$feature]))
			{
				static::$featuresCache[$feature] = Feature::isFeatureEnabled($feature);
			}

			return static::$featuresCache[$feature];
		}

		return true;
	}

	public static function canUseAutomation()
	{
		foreach (static::$supportedEntityTypes as $entityTypeId)
		{
			if (static::isAutomationAvailable($entityTypeId))
				return true;
		}
		return false;
	}

	public static function isSupported($entityTypeId)
	{
		return in_array((int)$entityTypeId, static::$supportedEntityTypes, true);
	}

	public static function runOnAdd($entityTypeId, $entityId)
	{
		//We need to ignore license restrictions in Simple CRM mode for Leads
		$ignoreLicense = false;
		if ($entityTypeId === \CCrmOwnerType::Lead && !LeadSettings::isEnabled())
		{
			$ignoreLicense = true;
		}

		if (empty($entityId) || !static::isAutomationAvailable($entityTypeId, $ignoreLicense))
			return;

		$automationTarget = static::createTarget($entityTypeId);
		$automationTarget->setEntityById($entityId);
		$automationTarget->getRuntime()->onEntityAdd();
	}

	public static function runOnStatusChanged($entityTypeId, $entityId)
	{
		if (empty($entityId) || !static::isAutomationAvailable($entityTypeId))
			return;

		$automationTarget = static::createTarget($entityTypeId);
		$automationTarget->setEntityById($entityId);
		$automationTarget->getRuntime()->onEntityStatusChanged();
	}

	/**
	 * Create Target instance by entity type.
	 * @param int $entityTypeId Entity type id from \CCrmOwnerType.
	 * @return Target\BaseTarget Target instance, child of BaseTarget.
	 * @throws NotSupportedException
	 */
	public static function createTarget($entityTypeId)
	{
		$entityTypeId = (int)$entityTypeId;

		if ($entityTypeId === \CCrmOwnerType::Deal)
		{
			return new Target\DealTarget();
		}
		elseif ($entityTypeId === \CCrmOwnerType::Lead)
		{
			return new Target\LeadTarget();
		}
		else
		{
			$entityTypeName = \CCrmOwnerType::ResolveName($entityTypeId);
			throw new NotSupportedException("Entity '{$entityTypeName}' not supported in current context.");
		}
	}

	/**
	 * Create Runtime instance.
	 * @return Engine\Runtime Runtime instance.
	 * @throws NotSupportedException
	 */
	public static function createRuntime()
	{
		return new Engine\Runtime();
	}

	/**
	 * @return Trigger\BaseTrigger[] Registered triggers array.
	 */
	private static function getTriggerRegistry()
	{
		if (self::$triggerRegistry === null)
		{
			$lead = \CCrmOwnerType::Lead;
			$deal = \CCrmOwnerType::Deal;

			self::$triggerRegistry = array(
				Trigger\EmailTrigger::className() => array($lead, $deal),
				Trigger\CallTrigger::className() => array($lead, $deal),
				Trigger\WebFormTrigger::className() => array($lead, $deal),
				Trigger\InvoiceTrigger::className() => array($deal),
				Trigger\WebHookTrigger::className() => array($lead, $deal),
				Trigger\VisitTrigger::className() => array($lead, $deal),
				Trigger\GuestReturnTrigger::className() => array($lead, $deal),
			);

			if (Trigger\OpenLineTrigger::isEnabled())
			{
				self::$triggerRegistry[Trigger\OpenLineTrigger::className()] = array($lead, $deal);
			}
			if (Trigger\AppTrigger::isEnabled())
			{
				self::$triggerRegistry[Trigger\AppTrigger::className()] = array($lead, $deal);
			}
		}

		return self::$triggerRegistry;
	}

	/**
	 * @param $entityTypeId Entity type id.
	 * @return array
	 */
	public static function getAvailableTriggers($entityTypeId)
	{
		$entityTypeId = (int)$entityTypeId;
		$description = array();
		/**
		 * @var BaseTrigger $triggerClass
		 * @var array $entityTypes
		 */
		foreach (self::getTriggerRegistry() as $triggerClass => $entityTypes)
		{
			if (!in_array($entityTypeId, $entityTypes, true))
				continue;

			$description[] = $triggerClass::toArray();
		}

		return $description;
	}

	/**
	 * @param $code Trigger string code.
	 * @return bool|Trigger\BaseTrigger Trigger class name or false.
	 */
	public static function getTriggerByCode($code)
	{
		$code = (string)$code;

		foreach (self::getTriggerRegistry() as $triggerClass => $entityTypes)
			if ($triggerClass::getCode() === $code)
				return $triggerClass::className();

		return false;
	}

	/**
	 * @param int $entityTypeId Entity type id.
	 * @param string $entityStatus Entity status for check.
	 * @return bool
	 */
	public static function hasRobotsForStatus($entityTypeId, $entityStatus)
	{
		if (!Helper::isBizprocEnabled() || !static::isSupported($entityTypeId))
			return false;

		$iterator = Engine\Entity\TemplateTable::getList(array(
			'filter' => array(
				'=ENTITY_TYPE_ID' => (int)$entityTypeId,
				'=ENTITY_STATUS'  => (string)$entityStatus
			)
		));
		$templateData = $iterator->fetch();

		if ($templateData)
		{
			$template = new Engine\Template($templateData);
			return ($template->isExternalModified() || count($template->getRobots()) > 0);
		}
		return false;
	}
}