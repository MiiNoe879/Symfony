<?php

namespace Thurly\Main\Web;

use Thurly\Main\Application;
use Thurly\Main\Text\Encoding;
use Thurly\Main\Type;

class PostDecodeFilter implements Type\IRequestFilter
{
	/**
	 * @param array $values
	 * @return array
	 */
	public function filter(array $values)
	{
		if(Application::getInstance()->isUtfMode())
		{
			return null;
		}
		if(empty($values['post']) || !is_array($values['post']))
		{
			return null;
		}

		return array(
			'post' => Encoding::convertEncoding($values['post'], 'UTF-8', SITE_CHARSET),
		);
	}
}