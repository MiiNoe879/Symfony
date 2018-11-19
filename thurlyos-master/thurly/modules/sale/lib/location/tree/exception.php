<?
namespace Thurly\Sale\Location\Tree;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Exception extends \Thurly\Sale\Location\Exception
{
	public function getDefaultMessage()
	{
		return Loc::getMessage('SALE_TREE_ENTITY_EXCEPTION');
	}

	protected function fillMessageAdditions()
	{
		$message = '';

		$aInfo = $this->getAdditionalInfo();

		if(isset($aInfo['ID']))
		{
			$message .= ' (ID = '.intval($aInfo['ID']).')';
		}
		if(isset($aInfo['CODE']))
		{
			$message .= ' (CODE = '.intval($aInfo['CODE']).')';
		}

		return $message;
	}
}

class NodeNotFoundException extends \Thurly\Sale\Location\Tree\Exception
{
	public function getDefaultMessage()
	{
		return Loc::getMessage('SALE_TREE_ENTITY_NODE_NOT_FOUND_EXCEPTION').static::fillMessageAdditions();
	}
}

class NodeIncorrectException extends \Thurly\Sale\Location\Tree\Exception
{
	public function getDefaultMessage()
	{
		return 'Incorrect LEFT_MARGIN or RIGHT_MARGIN (wrong data given or tree structure integrity seems to be compromised)'.static::fillMessageAdditions();
	}
}