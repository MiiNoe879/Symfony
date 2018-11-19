<?php
namespace Thurly\MessageService;

class MessageType
{
	const SMS = 'SMS';

	public static function isSupported($type)
	{
		return $type === static::SMS;
	}
}