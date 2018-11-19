<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arResult */
if ($arResult["page"])
{
	$page = $arResult["page"];
	$pageDir = new \Thurly\Main\IO\Directory(\Thurly\Main\Application::getDocumentRoot() . $this->GetFolder() . "/pages");
	$pages = $pageDir->getChildren();
	foreach ($pages as $pageFile)
	{
		/**
		 * @var $pageFile \Thurly\Main\IO\File
		 */

		$pageID = basename($pageFile->getName(), ".php");

		if ($pageID == $page)
		{
			$arResult["page_path"] = $pageDir->getPhysicalPath() . "/" . $pageFile->getName();
			break;
		}
	}
}