<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Dispatcher;

use Thurly\Main\Localization\Loc;

abstract class RestrictedAction extends PublicAction
{
	public function canExecute()
	{
		if(!\Thurly\Tasks\Util\Restriction::canManageTask())
		{
			$this->errors->add('ACTION_NOT_ALLOWED.RESTRICTED', Loc::getMessage('TASKS_RESTRICTED'));
			return false;
		}

		return true;
	}
}