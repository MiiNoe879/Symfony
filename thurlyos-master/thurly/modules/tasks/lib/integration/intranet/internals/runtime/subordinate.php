<?
namespace Thurly\Tasks\Integration\Intranet\Internals\Runtime;

use Thurly\Main\Entity;
use Thurly\Intranet\Internals\SubordinationCacheTable;

class Subordinate extends \Thurly\Tasks\Integration\Intranet
{
	public static function getSubordinateFilter($parameters)
	{
		if(!static::includeModule())
		{
			return array();
		}

		$rf = $parameters['REF_FIELD'];

		return array('runtime' =>
				array(
					new Entity\ReferenceField(
						'ISB',
						SubordinationCacheTable::getEntity(),
						array(
							'=ref.DIRECTOR' => array('?', $parameters['USER_ID']),
							'=ref.SUBORDINATE' => (((string) $rf != '' ? $rf : 'this')).'.USER_ID',
						),
						array('join_type' => 'inner')
					)
				)
			);
	}
}