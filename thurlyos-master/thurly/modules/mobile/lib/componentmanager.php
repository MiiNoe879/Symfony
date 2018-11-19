<?

namespace Thurly\Mobile;

use Thurly\Main\Application;
use Thurly\Main\IO\Directory;
use Thurly\Main\IO\File;
use Thurly\Main\Localization;
use Thurly\Main\Web\Json;

class ComponentManager
{
	private static $componentPath = "/thurly/components/thurly/mobile.jscomponent/jscomponents/";

	public static function getComponentVersion($componentName)
	{
		$componentFolder = new Directory(Application::getDocumentRoot() . self::$componentPath . $componentName);
		$versionFile = new File($componentFolder->getPath()."/version.php");
		if($versionFile->isExists())
		{
			$versionDesc = include($versionFile->getPath());
			return $versionDesc["version"];
		}

		return 1;
	}

	public static function getComponentPath($componentName)
	{
		return "/mobile/mobile_component/$componentName/?version=". self::getComponentVersion($componentName);
	}

}