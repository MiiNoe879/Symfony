<?
/**
 * Disk module integration in a context of Rest
 */

namespace Thurly\Tasks\Integration\Rest\Integration;

use Thurly\Disk\Driver;
use Thurly\Disk\Uf\FileUserType;
use Thurly\Disk\Rest\Externalizer;

use Thurly\Main\Loader;

final class Disk
{
	public static function getFileData($fileNodeId, $parameters = array())
	{
		if(!is_array($parameters))
		{
			$parameters = array();
		}

		$result = array(
			'ATTACHMENT_ID' => $fileNodeId
		);

		if(!isset($parameters['SERVER']) || !($parameters['SERVER'] instanceof \CRestServer))
		{
			return $result;
		}

		if(!Loader::includeModule('disk'))
		{
			return $result;
		}

		$fileNodeId = intval($fileNodeId);
		if(!$fileNodeId)
		{
			return $result;
		}

		$result['DOWNLOAD_URL'] = Driver::getInstance()->getUrlManager()->getUrlUfController('download', array('attachedId' => $fileNodeId, 'auth' => $parameters['SERVER']->getAuth()));

		return $result;
	}
}