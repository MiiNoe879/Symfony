<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Thurly\Main\Application;
use Thurly\Main\IO\Directory;
use Thurly\Main\IO\File;
use Thurly\Main\Localization;
use Thurly\Main\Web\Json;

/**
 * Class MobileJSComponent
 */
class MobileJSComponent extends \CThurlyComponent
{
	private static $jsComponentsFolder = "/jscomponents/";
	public $jsComponentPath;
	public $jsComponentName;
	protected $availableComponents;
	protected $jsComponentsPath;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$componentsPath = Application::getDocumentRoot() . $this->getPath() . self::$jsComponentsFolder;
		$this->availableComponents = array();
		$componentDir = new Directory($componentsPath);
		$jsComponentsDirs = $componentDir->getChildren();

		foreach ($jsComponentsDirs as $jsComponentDir)
		{
			if ($jsComponentDir->isDirectory())
			{
				$this->availableComponents[] = $jsComponentDir->getName();
			}
		}
	}


	public function onPrepareComponentParams($arParams)
	{
		if ($arParams["componentName"])
		{
			$this->jsComponentPath =  $this->getPath() . self::$jsComponentsFolder .$arParams["componentName"];
			$this->jsComponentName = $arParams["componentName"];
		}

		return $arParams;
	}


	public function executeComponent()
	{

		if (!in_array($this->jsComponentName, $this->availableComponents))
		{
			header('Content-Type: text/javascript');
			header("BX-Component-Not-Found: true");
			echo <<<JS
console.warn("Component not found");
JS;
		}
		else
		{
			$componentPath = Application::getDocumentRoot().$this->jsComponentPath;
			$componentFolder = new Directory($componentPath);
			$jsResult = "{}";

			if ($componentFolder->isExists())
			{
				if (Application::getInstance()->getContext()->getServer()->getRequestMethod() != "HEAD")
				{
					$jsComponentFile = new File($componentFolder->getPath() . "/component.js");

					if(!$jsComponentFile->isExists())
					{
						echo "File 'component.js' is not found";
						return;
					}
					$componentFile = new File($componentFolder->getPath() . "/component.php");
					if ($componentFile->isExists())
					{
						$componentResult = include($componentFile->getPath());
						$jsResult = $this->jsonEncode($componentResult);
					}


					$langPhrases = Localization\Loc::loadLanguageFile($componentPath . "/component.php");//component.php is not exists, but we use it to get php-langfile
					$jsonLangMessages = $this->jsonEncode($langPhrases);
					$jsComponent = $this->jsonEncode(array(
						'path' => $this->jsComponentPath.'/',
						'folder'=> $this->getPath().'/'
					));
					$js = <<<JS
BX.message($jsonLangMessages);
var result = $jsResult;
var component = $jsComponent;
JS;
					header('Content-Type: text/javascript');
					header("BX-Component-Version: " . self::getComponentVersion($this->jsComponentName));
					header("BX-Component: true");
					echo $js . $jsComponentFile->getContents();
				}
			}
		}
	}

	public function getComponentVersion($componentName)
	{
		$componentFolder = new Directory($this->getPath() . self::$jsComponentsFolder . $componentName);
		$versionFile = new File($componentFolder->getPath() . "/version.php");
		if ($versionFile->isExists())
		{
			$versionDesc = include($versionFile->getPath());
			return $versionDesc["version"];
		}

		return 1;
	}

	public function jsonEncode($string)
	{
		$options = JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
		return Json::encode($string, $options);
	}



}