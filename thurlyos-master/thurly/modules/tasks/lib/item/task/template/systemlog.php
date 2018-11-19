<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task\Template;

use Thurly\Tasks\Util\Type;

final class SystemLog extends \Thurly\Tasks\Item\SubItem
{
	protected static function getParentConnectorField()
	{
		return 'TEMPLATE_ID';
	}

	public static function getDataSourceClass()
	{
		return '\\Thurly\\Tasks\\Internals\\Task\\Template\\SystemLogTable';
	}

	public function externalizeFieldValue($name, $value)
	{
		if($name == 'DATA')
		{
			return Type::unSerializeArray($value);
		}

		return parent::externalizeFieldValue($name, $value);
	}

	public function internalizeFieldValue($name, $value)
	{
		if($name == 'DATA')
		{
			return Type::serializeArray($value);
		}

		return $value;
	}
}