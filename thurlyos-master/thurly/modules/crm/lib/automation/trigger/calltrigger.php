<?php
namespace Thurly\Crm\Automation\Trigger;

Use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CallTrigger extends BaseTrigger
{
	public static function getCode()
	{
		return 'CALL';
	}

	public static function getName()
	{
		return Loc::getMessage('CRM_AUTOMATION_TRIGGER_CALL_NAME');
	}

	public function checkApplyRules(array $trigger)
	{
		//Trigger has no rules yet.
		return true;
	}
}