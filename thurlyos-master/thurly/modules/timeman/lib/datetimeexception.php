<?php
namespace Thurly\Timeman;

use Thurly\Main\Loader;
use Thurly\Rest\RestException;

Loader::includeModule('rest');

class DateTimeException extends RestException
{
	const ERROR_WRONG_DATETIME_FORMAT = 'WRONG_DATETIME_FORMAT';
	const ERROR_WRONG_DATETIME = 'WRONG_DATETIME';
}