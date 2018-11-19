<?php

namespace Thurly\Main\Type\Contract;

interface Jsonable
{
	/**
	 * @return array
	 */
	public function toJson($options = 0);
}
