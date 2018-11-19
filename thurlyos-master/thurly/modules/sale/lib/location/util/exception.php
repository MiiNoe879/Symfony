<?
namespace Thurly\Sale\Location\Util;

use Thurly\Main;
use Thurly\Main\Localization\Loc;

class SaleTreeNodeNotFoundException extends Main\SystemException {

	public function __construct($message = "", $code = 0)
	{
		parent::_construct(strlen($message) ? $message : Loc::getMessage('SALE_TREE_ENTITY_NODE_NOT_FOUND_EXCEPTION'), $code);
	}

}

class SaleTreeSystemException extends Main\SystemException {

	public function __construct($message = "", $code = 0)
	{
		parent::_construct(strlen($message) ? $message : Loc::getMessage('SALE_TREE_ENTITY_INTERNAL_EXCEPTION'), $code);
	}

}
