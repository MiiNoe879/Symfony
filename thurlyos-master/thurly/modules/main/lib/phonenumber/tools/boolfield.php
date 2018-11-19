<?php

namespace Thurly\Main\PhoneNumber\Tools;

class BoolField extends XmlField
{
	public function decodeValue($value)
	{
		return ($value === 'true');
	}
}