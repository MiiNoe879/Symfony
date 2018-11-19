<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2012 Thurly
 */

namespace Thurly\Main\Data;

/**
 * Class description
 * @package    thurly
 * @subpackage main
 */
class MemcachedConnection extends NosqlConnection
{
	protected $host = 'localhost';

	protected $port = '11211';

	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		// host validation
		if (array_key_exists('host', $configuration))
		{
			if (!is_string($configuration['host']) || $configuration['host'] == "")
			{
				throw new \Thurly\Main\Config\ConfigurationException("Invalid host parameter");
			}

			$this->host = $configuration['host'];
		}

		// port validation
		if (array_key_exists('port', $configuration))
		{
			if (!is_string($configuration['port']) || $configuration['port'] == "")
			{
				throw new \Thurly\Main\Config\ConfigurationException("Invalid port parameter");
			}

			$this->port = $configuration['port'];
		}
	}

	protected function connectInternal()
	{
		$this->resource = new \Memcached;
		$this->isConnected = $this->resource->addServer($this->host, $this->port);
	}

	protected function disconnectInternal()
	{
	}

	public function get($key)
	{
		$this->connectInternal();

		return $this->resource->get($key);
	}

	public function set($key, $value)
	{
		$this->connectInternal();

		return $this->resource->set($key, $value);
	}
}
