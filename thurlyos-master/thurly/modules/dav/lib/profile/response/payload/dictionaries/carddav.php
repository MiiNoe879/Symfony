<?php

namespace Thurly\Dav\Profile\Response\Payload\Dictionaries;

/**
 * Class CardDav
 * @package Thurly\Dav\Profile\Response\Payload\Dictionaries
 */
class CardDav extends ComponentBase
{
	const TEMPLATE_DICT_NAME = 'carddav';


	/**
	 * @return bool
	 */
	public function isAvailable()
	{
		return true;
	}

}