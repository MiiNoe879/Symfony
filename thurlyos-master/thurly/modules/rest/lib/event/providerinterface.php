<?php
namespace Thurly\Rest\Event;

interface ProviderInterface
{
	public static function instance();
	public function send(array $queryData);
}
