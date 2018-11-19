<?php
class CThurlyCloudCDNLocation
{
	private $name = "";
	private $proto = "";
	private $prefixes = /*.(array[int]string).*/ array();
	/** @var array[int]CThurlyCloudCDNClass $classes */
	private $classes = /*.(array[int]CThurlyCloudCDNClass).*/ array();
	/** @var array[int]CThurlyCloudCDNServerGroup $server_groups */
	private $server_groups = /*.(array[int]CThurlyCloudCDNServerGroup).*/ array();
	/**
	 *
	 * @return string
	 *
	 */
	public function getName()
	{
		return $this->name;
	}
	/**
	 *
	 * @return string
	 *
	 */
	public function getProto()
	{
		return $this->proto;
	}
	/**
	 *
	 * @return array[int]string
	 *
	 */
	public function getPrefixes()
	{
		return $this->prefixes;
	}
	/**
	 *
	 * @param array[int]string $prefixes
	 * @return CThurlyCloudCDNLocation
	 *
	 */
	public function setPrefixes($prefixes)
	{
		$this->prefixes = /*.(array[int]string).*/ array();
		if (is_array($prefixes))
		{
			foreach ($prefixes as $prefix)
			{
				$prefix = trim($prefix, " \t\n\r");
				if ($prefix != "")
					$this->prefixes[] = $prefix;
			}
		}
		return $this;
	}
	/**
	 *
	 * @param string $name
	 * @param string $proto
	 * @param array[int]string $prefixes
	 * @return void
	 *
	 */
	public function __construct($name, $proto, $prefixes)
	{
		$this->proto = $proto;
		$this->name = $name;
		$this->setPrefixes($prefixes);
	}
	/**
	 *
	 * @return array[int]CThurlyCloudCDNClass
	 *
	 */
	public function getClasses()
	{
		return $this->classes;
	}
	/**
	 *
	 * @return array[int]CThurlyCloudCDNServerGroup
	 *
	 */
	public function getServerGroups()
	{
		return $this->server_groups;
	}
	/**
	 *
	 * @param CThurlyCloudCDNClass $file_class
	 * @param CThurlyCloudCDNServerGroup $server_group
	 * @return CThurlyCloudCDNLocation
	 *
	 */
	public function addService($file_class, $server_group)
	{
		if (is_object($file_class) && $file_class instanceof CThurlyCloudCDNClass && is_object($server_group) && $server_group instanceof CThurlyCloudCDNServerGroup)
		{
			$this->classes[] = $file_class;
			$this->server_groups[] = $server_group;
		}
		return $this;
	}
	/**
	 *
	 * @param CDataXMLNode $node
	 * @param CThurlyCloudCDNConfig $config
	 * @return CThurlyCloudCDNLocation
	 *
	 */
	public static function fromXMLNode(CDataXMLNode $node, CThurlyCloudCDNConfig $config)
	{
		$name = $node->getAttribute("name");
		$proto = $node->getAttribute("proto");
		$prefixes = /*.(array[int]string).*/ array();
		$nodePrefixes = $node->elementsByName("prefix");
		foreach ($nodePrefixes as $nodePrefix)
		{
			$prefixes[] = $nodePrefix->textContent();
		}
		$location = new CThurlyCloudCDNLocation($name, $proto, $prefixes);
		$nodeServices = $node->elementsByName("service");
		foreach ($nodeServices as $nodeService)
		{
			$file_class = $config->getClassByName($nodeService->getAttribute("class"));
			$server_group = $config->getServerGroupByName($nodeService->getAttribute("servergroup"));
			$location->addService($file_class, $server_group);
		}
		return $location;
	}
	/**
	 *
	 * @param string $name
	 * @param string $value
	 * @param CThurlyCloudCDNConfig $config
	 * @return CThurlyCloudCDNLocation
	 *
	 */
	public static function fromOptionValue($name, $value, CThurlyCloudCDNConfig $config)
	{
		$values = unserialize($value);
		$proto = "";
		$prefixes = /*.(array[int]string).*/ array();
		$services = /*.(array[string]string).*/ array();
		if (is_array($values))
		{
			if (isset($values["prefixes"]) && is_array($values["prefixes"]))
			{
				foreach ($values["prefixes"] as $prefix)
					$prefixes[] = $prefix;
			}
			if (isset($values["services"]) && is_array($values["services"]))
			{
				$services = $values["services"];
			}
			if (isset($values["proto"]))
			{
				$proto = $values["proto"];
			}
		}
		$location = new CThurlyCloudCDNLocation($name, $proto, $prefixes);
		foreach ($services as $file_class => $server_group)
		{
			$location->addService($config->getClassByName($file_class), $config->getServerGroupByName($server_group));
		}
		return $location;
	}
	/**
	 *
	 * @return string
	 *
	 */
	public function getOptionValue()
	{
		$services = /*.(array[string]string).*/ array();
		foreach ($this->classes as $i => $file_class)
		{
			/* @var CThurlyCloudCDNClass $file_class */
			$class_name = $file_class->getName();
			/* @var CThurlyCloudCDNServerGroup $server_group */
			$server_group = $this->server_groups[$i];
			$services[$class_name] = $server_group->getName();
		}
		return serialize(array(
			"proto" => $this->proto,
			"prefixes" => $this->prefixes,
			"services" => $services,
		));
	}
	/**
	 *
	 * @param string $p_prefix
	 * @param string $p_extension
	 * @param string $p_link
	 * @return string
	 *
	 */
	public function getServerNameByPrefixAndExtension($p_prefix, $p_extension, $p_link)
	{
		foreach ($this->prefixes as $prefix)
		{
			if ($p_prefix === $prefix)
			{
				foreach ($this->classes as $i => $file_class)
				{
					/* @var CThurlyCloudCDNClass $file_class */
					foreach ($file_class->getExtensions() as $extension)
					{
						if (strtolower($p_extension) === $extension)
						{
							/* @var CThurlyCloudCDNServerGroup $server_group */
							$server_group = $this->server_groups[$i];
							$servers = $server_group->getServers();
							if (!empty($servers))
							{
								$j = intval(abs(crc32($p_link))) % count($servers);
								return $servers[$j];
							}
						}
					}
				}
			}
		}
		return "";
	}
}
class CThurlyCloudCDNLocations implements Iterator
{
	private $locations = /*.(array[string]CThurlyCloudCDNLocation).*/ array();
	/**
	 *
	 * @param CThurlyCloudCDNLocation $location
	 * @return CThurlyCloudCDNLocations
	 *
	 */
	public function addLocation(CThurlyCloudCDNLocation $location)
	{
		$this->locations[$location->getName()] = $location;
		return $this;
	}
	/**
	 *
	 * @param string $location_name
	 * @return CThurlyCloudCDNLocation
	 *
	 */
	public function getLocationByName($location_name)
	{
		return $this->locations[$location_name];
	}
	/**
	 *
	 * @param CDataXMLNode $node
	 * @param CThurlyCloudCDNConfig $config
	 * @return CThurlyCloudCDNLocations
	 *
	 */
	public static function fromXMLNode(CDataXMLNode $node, CThurlyCloudCDNConfig $config)
	{
		$locations = new CThurlyCloudCDNLocations;
		foreach ($node->children() as $sub_node)
		{
			$locations->addLocation(CThurlyCloudCDNLocation::fromXMLNode($sub_node, $config));
		}
		return $locations;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @param CThurlyCloudCDNConfig $config
	 * @return CThurlyCloudCDNLocations
	 *
	 */
	public static function fromOption(CThurlyCloudOption $option, CThurlyCloudCDNConfig $config)
	{
		$locations = new CThurlyCloudCDNLocations;
		foreach ($option->getArrayValue() as $location_name => $location_value)
		{
			$locations->addLocation(CThurlyCloudCDNLocation::fromOptionValue($location_name, $location_value, $config));
		}
		return $locations;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @return CThurlyCloudCDNLocations
	 *
	 */
	public function saveOption(CThurlyCloudOption $option)
	{
		$locations = /*.(array[string]string).*/ array();
		foreach ($this->locations as $location_name => $location)
		{
			/* @var CThurlyCloudCDNLocation $location */
			$locations[$location_name] = $location->getOptionValue();
		}
		$option->setArrayValue($locations);
		return $this;
	}
	
	function rewind()
	{
		reset($this->locations);
	}
	
	function current()
	{
		return current($this->locations);
	}
	
	function key()
	{
		return key($this->locations);
	}
	
	function next()
	{
		next($this->locations);
	}
	
	function valid()
	{
		return key($this->locations) !== null;
	}
}
