<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 17.01.17
 * Time: 10:43
 */

namespace Thurly\Rest;


class HandlerHelper
{
	const ERROR_UNSUPPORTED_PROTOCOL = 'ERROR_UNSUPPORTED_PROTOCOL';
	const ERROR_WRONG_HANDLER_URL = 'ERROR_WRONG_HANDLER_URL';
	const ERROR_HANDLER_URL_MATCH = 'ERROR_HANDLER_URL_MATCH';

	protected static $applicationList = array();

	/**
	 * Checks callback URL validity.
	 *
	 * @param string $handlerUrl Callback URL.
	 * @param array $appInfo Application info.
	 * @param bool|true $checkInstallUrl Flag, whether to check URL_INSTALL field.
	 *
	 * @return bool
	 *
	 * @throws RestException
	 */
	public static function checkCallback($handlerUrl, $appInfo, $checkInstallUrl = true)
	{
		$callbackData = parse_url($handlerUrl);

		if(is_array($callbackData)
			&& strlen($callbackData['host']) > 0
			&& strpos($callbackData['host'], '.') > 0
		)
		{
			if($callbackData['scheme'] == 'http' || $callbackData['scheme'] == 'https')
			{
				$host = $callbackData['host'];

				if(strlen($appInfo['URL']) > 0)
				{
					$urlsList = array($appInfo['URL']);

					if(strlen($appInfo['URL_DEMO']) > 0)
					{
						$urlsList[] = $appInfo['URL_DEMO'];
					}

					if($checkInstallUrl && strlen($appInfo['URL_INSTALL']) > 0)
					{
						$urlsList[] = $appInfo['URL_INSTALL'];
					}

					foreach($urlsList as $url)
					{
						$a = parse_url($url);
						if($host == $a['host'] || $a['host'] == 'localhost')
						{
							return true;
						}
					}

					throw new RestException('Handler URL host doesn\'t match application URL', static::ERROR_HANDLER_URL_MATCH);
				}
				else
				{
					return true;
				}
			}
			else
			{
				throw new RestException('Unsupported handler protocol', static::ERROR_UNSUPPORTED_PROTOCOL);
			}
		}
		else
		{
			throw new RestException('Wrong handler URL', static::ERROR_WRONG_HANDLER_URL);
		}
	}

	public static function storeApplicationList($PLACEMENT, $applicationList)
	{
		static::$applicationList[$PLACEMENT] = $applicationList;
	}

	public static function getApplicationList($PLACEMENT)
	{
		return static::$applicationList[$PLACEMENT];
	}
}