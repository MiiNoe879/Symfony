<?php
class CThurlyCloudCDNClass
{
	private $name = "";
	private $extensions = /*.(array[int]string).*/ array();
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
	public function getExtensions()
	{
		return $this->extensions;
	}
	/**
	 *
	 * @param array[int]string $extensions
	 * @return CThurlyCloudCDNClass
	 *
	 */
	public function setExtensions($extensions)
	{
		$this->extensions = /*.(array[int]string).*/ array();
		if (is_array($extensions))
		{
			foreach ($extensions as $extension)
			{
				$extension = trim($extension, " \t\n\r");
				if ($extension != "")
					$this->extensions[] = $extension;
			}
		}
		return $this;
	}
	/**
	 *
	 * @param string $name
	 * @param array[int]string $extensions
	 * @return void
	 */
	public function __construct($name, $extensions)
	{
		$this->name = $name;
		$this->setExtensions($extensions);
	}
	/**
	 *
	 * @param CDataXMLNode $node
	 * @return CThurlyCloudCDNClass
	 *
	 */
	public static function fromXMLNode(CDataXMLNode $node)
	{
		$name = $node->getAttribute("name");
		$extensions = /*.(array[int]string).*/ array();
		$nodeExtensions = $node->elementsByName("extension");
		foreach ($nodeExtensions as $nodeExtension)
		{
			$extensions[] = $nodeExtension->textContent();
		}
		return new CThurlyCloudCDNClass($name, $extensions);
	}
}
class CThurlyCloudCDNClasses
{
	private $classes = /*.(array[string]CThurlyCloudCDNClass).*/ array();
	/**
	 *
	 * @param CThurlyCloudCDNClass $file_class
	 * @return CThurlyCloudCDNClasses
	 *
	 */
	public function addClass(CThurlyCloudCDNClass $file_class)
	{
		$this->classes[$file_class->getName()] = $file_class;
		return $this;
	}
	/**
	 *
	 * @param string $class_name
	 * @return CThurlyCloudCDNClass
	 *
	 */
	public function getClass($class_name)
	{
		return $this->classes[$class_name];
	}
	/**
	 *
	 * @param CDataXMLNode $node
	 * @return CThurlyCloudCDNClasses
	 *
	 */
	public static function fromXMLNode(CDataXMLNode $node)
	{
		$classes = new CThurlyCloudCDNClasses;
		foreach ($node->children() as $sub_node)
		{
			$classes->addClass(CThurlyCloudCDNClass::fromXMLNode($sub_node));
		}
		return $classes;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @return CThurlyCloudCDNClasses
	 *
	 */
	public static function fromOption(CThurlyCloudOption $option)
	{
		$classes = new CThurlyCloudCDNClasses;
		foreach ($option->getArrayValue() as $class_name => $extensions)
		{
			$classes->addClass(new CThurlyCloudCDNClass($class_name, explode(",", $extensions)));
		}
		return $classes;
	}
	/**
	 *
	 * @param CThurlyCloudOption $option
	 * @return CThurlyCloudCDNClasses
	 *
	 */
	public function saveOption(CThurlyCloudOption $option)
	{
		$classes = /*.(array[string]string).*/ array();
		foreach ($this->classes as $class_name => $file_class)
		{
			/* @var CThurlyCloudCDNClass $file_class */
			$classes[$class_name] = implode(",", $file_class->getExtensions());
		}
		$option->setArrayValue($classes);
		return $this;
	}
}
