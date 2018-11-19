<?php

namespace Thurly\Sale\Cashbox;

use Thurly\Sale\Result;

interface ICheckable
{
	/**
	 * @param Check $check
	 * @return Result
	 */
	public function check(Check $check);
}

