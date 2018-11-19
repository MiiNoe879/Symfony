<?php
class CThurlyCloudCDNServerGroup
{
	private $name = "";
	private $servers = /*.(array[int]string).*/ array();
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
	 * @return array[int]string
	 *
	 */
	public function getServers()
	{
		return $this->servers;
	}
	/**
	 *
	 * @param array[int]string $servers
	 * @return CThurlyCloudCDNServerGroup
	 *
	 */
	public function setServers($servers)
	{
		$this->servers = /*.(array[int]string).*/ array();
		if (is_array($servers))
		{
			foreach ($servers as $server)
			{
				$server = trim($server, " \t\n\r");
				if ($server != "")
					$this->servers[] = $server;
			}
		}
		return $this;
	}
	/**
	 *
	 * @param string $name
	 * @param array[int]string $servers
	 * @return void
	 *
	 */
	public function __construct($name, $servers)
	{
		$this->name = $name;
		$this->setServers($servers);
	}
	/**
	 *
	 * @param CDataXMLNode $node
	 * @return CThurlyCloudCDNServerGroup
	 *
	 */
	public static function fromXMLNode(CDataXMLNode $node)
	{
		$name = $node->getAttribute("name");
		$servers = /*.(array[int]string).*/ array();
		$nodeServers = $node->elementsByName("name");
		foreach ($nodeServers as $nodeServer)
		{
			$servers[] = $nodeServer->textContent();
		}
		return new CThurlyCloudCDNServerGroup($name, $servers);
	}
}
class CThurlyCloudCDNServerGroups
{
	/** @var array[string]CThurlyCloudCDNServerGroup $groups */
	private $groups = /*.(array[string]CThurlyCloudCDNServerGroup).*/ array();
	/**
	 *
	 * @param CThurlyCloudCDNServerGroup $group
	 * @return CThurlyCloudCDNServerGroups
	 *
	 */
	public function addGroup(CThurlyCloudCDNServerGroup $group)
	{
		$this->groups[$group->getName()] = $group;
		return $this;
	}
	/**
	 *
	 * @param string $group_name
	 * @return CThurlyCloudCDNServerGroup
	 *
	 */
	public function getGroup($group_name)
	{
		return $this->groups[$group_name];
	}
	/**
	 *
	 * @param CDataXMLNode $node
	 * @return CThurlyCloudCDNServerGroups
	 *
	 */
	public static function fromXMLNode(CDataXMLNode $node)
	{
		$groups = new CThurlyCloudCDNServerGroups;
		foreach ($node->children() as $sub_node)
		{
			$groups->addGroup(CThurlyCloudCDNServerGroup::fromXMLNode($sub_node));
		}
		return $groups;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @return CThurlyCloudCDNServerGroups
	 *
	 */
	public static function fromOption(CThurlyCloudOption $option)
	{
		$groups = new CThurlyCloudCDNServerGroups;
		foreach ($option->getArrayValue() as $group_name => $servers)
		{
			$groups->addGroup(new CThurlyCloudCDNServerGroup($group_name, explode(",", $servers)));
		}
		return $groups;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @return CThurlyCloudCDNServerGroups
	 *
	 */
	public function saveOption(CThurlyCloudOption $option)
	{
		$groups = /*.(array[string]string).*/ array();
		foreach ($this->groups as $group_name => $group)
		{
			/* @var CThurlyCloudCDNServerGroup $group */
			$groups[$group_name] = implode(",", $group->getServers());
		}
		$option->setArrayValue($groups);
		return $this;
	}
}
