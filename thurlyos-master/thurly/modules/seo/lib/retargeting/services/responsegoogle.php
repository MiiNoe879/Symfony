<?

namespace Thurly\Seo\Retargeting\Services;

use \Thurly\Main\Error;
use \Thurly\Main\Web\Json;
use \Thurly\Seo\Retargeting\Response;


class ResponseGoogle extends Response
{
	const TYPE_CODE = 'google';

	protected function getSkippedErrorCodes()
	{
		return array(
			'400' // invalid_parameter: segment data not modified
		);
	}

	public function parse($data)
	{
		if (!is_array($data))
		{
			$data = array();
		}

		$this->setData($data);
	}
}