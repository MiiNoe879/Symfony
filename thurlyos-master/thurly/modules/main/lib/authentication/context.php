<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2017 Thurly
 */
namespace Thurly\Main\Authentication;

class Context
{
	protected $userId;

	public function __construct()
	{
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}
}
