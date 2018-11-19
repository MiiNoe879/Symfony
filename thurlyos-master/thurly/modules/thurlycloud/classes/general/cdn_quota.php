<?php
class CThurlyCloudCDNQuota
{
	private $expires = 0;
	private $allowed = 0.0;
	private $traffic = 0.0;
	/**
	 *
	 * @return float
	 *
	 */
	public function getAllowedSize()
	{
		return $this->allowed;
	}
	/**
	 *
	 * @return float
	 *
	 */
	public function getTrafficSize()
	{
		return $this->traffic;
	}
	/**
	 * Checks if it is time to update quota info
	 *
	 * @return bool
	 *
	 */
	public function isExpired()
	{
		return $this->expires < time();
	}
	/**
	 *
	 * @param int $expires
	 * @return CThurlyCloudCDNQuota
	 *
	 */
	public function setExpires($expires)
	{
		$this->expires = intval($expires);
		if ($this->expires < 0)
			$this->expires = 0;

		return $this;
	}
	/**
	 *
	 * @param float $allowed
	 * @return CThurlyCloudCDNQuota
	 *
	 */
	public function setAllowedSize($allowed)
	{
		$this->allowed = doubleval($allowed);
		if ($this->allowed < 0.0)
			$this->allowed = 0.0;

		return $this;
	}
	/**
	 *
	 * @param float $traffic
	 * @return CThurlyCloudCDNQuota
	 *
	 */
	public function setTrafficSize($traffic)
	{
		$this->traffic = doubleval($traffic);
		if ($this->traffic < 0.0)
			$this->traffic = 0.0;

		return $this;
	}
	/**
	 *
	 * @param string $str
	 * @return float
	 *
	 */
	public static function parseSize($str)
	{
		$str = strtolower($str);
		$res = doubleval($str);
		$suffix = substr($str, -1);
		if ($suffix === "k")
			$res*= 1024;
		elseif ($suffix === "m")
			$res*= 1048576;
		elseif ($suffix === "g")
			$res*= 1048576 * 1024;

		return $res;
	}
	/**
	 *
	 * @param CDataXMLNode $node
	 * @return CThurlyCloudCDNQuota
	 *
	 */
	public static function fromXMLNode(CDataXMLNode $node)
	{
		$quota = new CThurlyCloudCDNQuota();
		$quota->setExpires(strtotime($node->getAttribute("expires")));
		$allow_nodes = $node->elementsByName("allow");
		foreach ($allow_nodes as $allow_node)
			$quota->setAllowedSize(self::parseSize($allow_node->textContent()));

		$traffic_nodes = $node->elementsByName("traffic");
		foreach ($traffic_nodes as $traffic_node)
			$quota->setTrafficSize(self::parseSize($traffic_node->textContent()));

		return $quota;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @return CThurlyCloudCDNQuota
	 *
	 */
	public static function fromOption(CThurlyCloudOption $option)
	{
		$quota = new CThurlyCloudCDNQuota();
		$values = $option->getArrayValue();
		$quota->setExpires(intval($values["expires"]));
		$quota->setAllowedSize(doubleval($values["allow"]));
		$quota->setTrafficSize(doubleval($values["traffic"]));
		return $quota;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @return CThurlyCloudCDNQuota
	 *
	 */
	public function saveOption(CThurlyCloudOption $option)
	{
		$values = array(
			"expires" => (string)$this->expires,
			"allow" => (string)$this->allowed,
			"traffic" => (string)$this->traffic,
		);
		$option->setArrayValue($values);
		return $this;
	}
}
