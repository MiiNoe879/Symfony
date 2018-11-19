<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ListsInterfaceForm  extends CThurlyComponent
{
	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}