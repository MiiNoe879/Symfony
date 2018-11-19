<?php

namespace Thurly\Sale\Cashbox;

use Thurly\Sale\Result;

interface IPrintImmediately
{
	/**
	 * @param Check $check
	 * @return Result
	 */
	public function printImmediately(Check $check);
}

