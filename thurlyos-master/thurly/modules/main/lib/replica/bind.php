<?php
namespace Thurly\Main\Replica;

class Bind
{
	/**
	 * Initializes replication process on main side.
	 *
	 * @return void
	 */
	public function start()
	{
		\Thurly\Replica\Client\HandlersManager::register(new UrlMetadataHandler());
	}
}
