<?php

use Thurly\Main;
use Thurly\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if(!\Thurly\Main\Loader::includeModule('advertising'))
	return;

Loc::loadMessages(__FILE__);

class AdvertisingBannerView extends \CThurlyComponent
{
	public function onPrepareComponentParams($params)
	{
		if (is_array($params['FILES']) && $params['CASUAL_PROPERTIES']['TYPE'] == 'template')
		{
			foreach ($params['FILES'] as $name => $id)
			{
				if ($id !== 'null')
					$params['FILES'][$name] = CFile::GetFileArray($id);
			}
		}
		else if ($params['CASUAL_PROPERTIES']['IMG'] !== 'null')
		{
				$params['FILES']['CASUAL_IMG'] = CFile::GetFileArray(intval($params['CASUAL_PROPERTIES']['IMG']));
		}
		else
			$params['FILES'] = array();

		return $params;
	}

	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}