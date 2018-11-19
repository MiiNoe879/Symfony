<?php
namespace Thurly\Crm\Automation\Trigger;

Use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class WebFormTrigger extends BaseTrigger
{
	public static function getCode()
	{
		return 'WEBFORM';
	}

	public static function getName()
	{
		return Loc::getMessage('CRM_AUTOMATION_TRIGGER_WEBFORM_NAME');
	}

	public function checkApplyRules(array $trigger)
	{
		if (
			is_array($trigger['APPLY_RULES'])
			&& isset($trigger['APPLY_RULES']['form_id'])
			&& $trigger['APPLY_RULES']['form_id'] > 0
		)
		{
			return (int)$trigger['APPLY_RULES']['form_id'] === (int)$this->getInputData('WEBFORM_ID');
		}
		return true;
	}
}