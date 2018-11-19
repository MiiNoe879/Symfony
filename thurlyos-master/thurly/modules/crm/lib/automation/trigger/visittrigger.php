<?php
namespace Thurly\Crm\Automation\Trigger;

Use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class VisitTrigger extends BaseTrigger
{
	public static function getCode()
	{
		return 'VISIT';
	}

	public static function getName()
	{
		return Loc::getMessage('CRM_AUTOMATION_TRIGGER_VISIT_NAME');
	}

	public function checkApplyRules(array $trigger)
	{
		//Trigger has no rules yet.
		return true;
	}
}