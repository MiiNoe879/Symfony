<?

namespace Thurly\Seo\Retargeting\Services;

use \Thurly\Seo\Retargeting\Request;
use Thurly\Seo\Engine\Thurly as EngineThurly;

class RequestGoogle extends Request
{
	const TYPE_CODE = 'google';

	public function query(array $params = array())
	{
		$methodName = 'seo.client.ads.google.' . $params['methodName'];
		$parameters = $params['parameters'];
		$engine = new EngineThurly();
		if (!$engine->isRegistered())
		{
			return false;
		}

		$response = $engine->getInterface()->getTransport()->call($methodName, $parameters);
		return (
			(isset($response['result']['RESULT']) && $response['result']['RESULT'])
				?
				$response['result']['RESULT']
				: array()
		);
	}
}