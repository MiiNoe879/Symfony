<?
/**
 * @internal
 * @access private
 */

namespace Thurly\Tasks\Internals\RunTime\Task;

use Thurly\Main\Entity;
use Thurly\Tasks\Internals\Task\ParameterTable;

final class Parameter extends \Thurly\Tasks\Internals\Runtime
{
	public static function getParameter(array $parameters)
	{
		$parameters = static::checkParameters($parameters);
		$rf = $parameters['REF_FIELD'];

		return array(
			'runtime' => array(
				new Entity\ReferenceField(
					'PARAMETER',
					ParameterTable::getEntity(),
					array(
						'=this.'.((string) $rf != '' ? $rf : 'ID') => 'ref.TASK_ID',
					) + (array_key_exists('CODE', $parameters) ? array(
						'=ref.CODE' => array('?', intval($parameters['CODE'])),
					) : array()),
					array(
						'join_type' => 'left'
					)
				)
			),
		);
	}
}