<?php
namespace Thurly\Crm\Automation\Trigger;

Use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class EmailTrigger extends BaseTrigger
{
	public static function getCode()
	{
		return 'EMAIL';
	}

	public static function getName()
	{
		return Loc::getMessage('CRM_AUTOMATION_TRIGGER_EMAIL_NAME');
	}

	public function checkApplyRules(array $trigger)
	{
		/*
		Email trigger has no rules yet.
		Future plan:
		- check topic
		- check special code
		*/

		return true;
	}
}