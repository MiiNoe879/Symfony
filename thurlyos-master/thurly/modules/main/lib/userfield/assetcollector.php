<?php
namespace Thurly\Main\UserField;

use Thurly\Main\Page\Asset;

/**
 * Thurly vars
 * @global \CMain $APPLICATION
 */

class AssetCollector
{
	protected $assetCollectionStarted = false;

	public function startAssetCollection()
	{
		if(!$this->assetCollectionStarted)
		{
			$this->resetAssets();
			$this->assetCollectionStarted = true;
		}
	}

	public function getCollectedAssets()
	{
		$result = array_merge(
			$this->parseResourceString(Asset::getInstance()->getJs()),
			$this->parseResourceString(Asset::getInstance()->getCss())
		);

		return array_unique($result);
	}

	protected function resetAssets()
	{
		global $APPLICATION;

		$APPLICATION->ShowAjaxHead(true, false, false, false);
	}

	protected function parseResourceString($string)
	{
		$result = array();
		$string = preg_split("/\\r\\n|\\r|\\n/", $string);
		foreach($string as $v)
		{
			$v = trim((string)$v);
			if($v !== '')
			{
				$result[] = $v;
			}
		}

		return array_unique($result);
	}
}