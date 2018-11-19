<?php

namespace Thurly\Dav\Profile\Response\Payload\Dictionaries;

use Thurly\Dav\TokensTable;
use Thurly\Main\UserTable;

/**
 * Class DecoratorBase
 * @package Thurly\Dav\Profile\Response\Payload\Dictionaries
 */
abstract class DecoratorBase extends Base
{
	protected $dictionaries;

	/**
	 * Constructor Base constructor.
	 * @param Base[] $dictionaries Collection of dictionaries to be rendered.
	 * @param string $accessToken Access token for request payload.
	 */
	public function __construct($dictionaries = array(), $accessToken)
	{
		$this->dictionaries = $dictionaries;
		$result = TokensTable::getById($accessToken)->fetch();
		$user = array();
		if ($result && TokensTable::isTokenValid($result['TOKEN']))
		{
			$user = UserTable::getById($result['USER_ID'])->fetch();
		}

		$this->setUser($user);
	}

	/**
	 * @return bool
	 */
	public function isAvailable()
	{
		$user = $this->getUser();
		return !empty($user);
	}
}